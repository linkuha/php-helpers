<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 29.12.2018
 * Time: 15:36
 */

namespace SimpleLibs;

final class FileHelper
{
    public static function getPhpParams()
    {
        return [
            "OS" => SystemHelper::getOS(),
            "file_get_contents" => function_exists("file_get_contents"),
            "allow_url_fopen" => ini_get('allow_url_fopen'),
            "SPL" => extension_loaded("SPL")
        ];
    }

    public static function dirAvailable($path)
    {
//        if (extension_loaded("SPL")) {
//            $fileObj = new \SplFileInfo($path);
//
//            if ($fileObj->isDir() && $fileObj->isReadable()) {
//                return true;
//            }
//            return false;
//        }
        if (is_dir($path) && is_readable($path)) {
            return true;
        }
        return false;
    }

    public static function fileAvailable($path)
    {
//        if (extension_loaded("SPL")) {
//            $fileObj = new \SplFileInfo($path);
//
//            if ($fileObj->isFile() && $fileObj->isReadable()) {
//                return true;
//            }
//            return false;
//        }
        if (is_file($path) && is_readable($path)) {
            return true;
        }
        return false;
    }

    public static function read($filename, $locking = false)
    {
//        if (extension_loaded("SPL")) {
//            $fileObj = new \SplFileObject(ENV_PATH);
//            return $fileObj->fread($fileObj->getSize());
//        }
        if ( ! function_exists("file_get_contents")) {
            if (ini_get('allow_url_fopen') == '1') {
                return implode('', file($filename));
            }

            $fp = fopen($filename, 'r');
            if ($fp) {
                $os = SystemHelper::getOS();
                if ($os === 'windows') {
                    // disable 8kb blocks reading
                    @set_file_buffer($fp, 0);
                }
                if ($locking && $os === 'linux') { flock($fp, LOCK_EX); }

                clearstatcache();
                $content = fread($fp, filesize($filename));

                if ($locking && $os === 'linux') { flock($fp, LOCK_UN); }
                fclose($fp);

                return $content;
            }
            return null;
        } else {
            return @file_get_contents($filename); // skip PHP Warning
        }
    }

    public static function write($filename, $content, $locking = false)
    {
        if ( ! function_exists("file_put_contents")) {
            $fp = fopen($filename, 'w');
            if ($fp) {
                $os = SystemHelper::getOS();
                if ($os === 'windows') {
                    // disable 8kb blocks reading
                    @set_file_buffer($fp, 0);
                }
                if ($locking && $os === 'linux') flock($fp, LOCK_EX);
                // here can ftruncate if need
                if ($locking && $os === 'linux') flock($fp, LOCK_UN);
                fclose($fp);

                return true;
            }
            return false;
        } else {
            @file_put_contents($filename, $content); // skip PHP Warning
            return true;
        }
    }

    public static function findAll($dirPath, $extension)
    {
        if ( ! extension_loaded("SPL")) {
            throw new \RuntimeException("SPL ext. not loaded for filesystem iterating.");
        }

        $fileList = [];
        /**
         * Variant 1
         */
        $iterator = new \FilesystemIterator($dirPath);
        $filter = new \RegexIterator($iterator, "/.*\\.{$extension}$/");

        foreach($filter as $entry) {
            /**
             * @var \SplFileInfo $entry
             */
            $fileList[] = [
                'name' => $entry->getFilename(),
                'path' => $entry->getPathname(),
            ];
        }

        /**
         * Variant 2
         */
//        $iterator2 = new \RecursiveIteratorIterator(
//            new \RecursiveDirectoryIterator($dirPath),
//            \RecursiveIteratorIterator::SELF_FIRST
//        );
//        $filter = new \RegexIterator($iterator2, '/^.+\.'.$extension.'$/');
//
//        foreach ($filter as $entry) {
//            $fileList[] = $entry->getPathname();
//        }

        /**
         * Variant 3
         */
//        $iterator3 = new \GlobIterator($dirPath.'/*/*\.'.$extension);
//
//        foreach ($iterator3 as $entry) {
//            $fileList[] = $entry->getPathname();
//        }

        return $fileList;
    }

    public static function getPermissions($path)
    {
        return decoct(fileperms($path) & 0777);
    }

    /**
     * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     * @author Torleif Berger, Lorenzo Stanco
     * @link http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     *
     * @param string $filepath
     * @param int $lines
     * @param bool $adaptive
     * @return bool|string
     */
    public static function tailCustom($filepath, $lines = 1, $adaptive = true)
    {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) { return false; }
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) { $buffer = 4096; }
        else { $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096)); }
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") { $lines -= 1; }

        // Start reading
        $output = '';
        $chunk = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        fclose($f);
        return trim($output);
    }

    public static function tailLinux($filepath, $lines)
    {
        if (self::fileAvailable($filepath)) { return false; }
        $lines = `tail -$lines $filepath`;
        return $lines;
    }
}
