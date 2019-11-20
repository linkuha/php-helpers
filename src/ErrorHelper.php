<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 03.02.2019
 * Time: 4:55
 */

namespace SimpleLibs;

class ErrorHelper
{
    const DEFAULT_LOG = "/tmp/manual_handled_errors.log";

    public static $logPath = self::DEFAULT_LOG;

    public static function changeLogPath($path = self::DEFAULT_LOG)
    {
        self::$logPath = $path;
    }

    public static function getLevels()
    {
        return get_defined_constants(true)["Core"];
    }

    public static function handleWarning(callable $function)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            file_put_contents(
                self::$logPath,
                date("Y-m-d H:i:s") . " WARNING ($errno): $errstr at $errfile:$errline init from {$_SERVER['SCRIPT_FILENAME']} " . self::getBacktraceEnd() . PHP_EOL,
                FILE_APPEND);
        }, E_WARNING);
        $function();
        restore_error_handler();
    }

    public static function handleNotice(callable $function)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            file_put_contents(
                self::$logPath,
                date("Y-m-d H:i:s") . " NOTICE ($errno): $errstr at $errfile:$errline init from {$_SERVER['SCRIPT_FILENAME']} " . self::getBacktraceEnd() . PHP_EOL,
                FILE_APPEND);
        }, E_NOTICE);
        $function();
        restore_error_handler();
    }

    public static function getBacktraceEnd()
    {
        $callStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $lastPoint = null;
        foreach ($callStack as $key => $point) {
            if ( ! isset($callStack[$key + 1])) {
                $lastPoint = $point["file"] . ":" . $point["line"];
            }
        }
        return $lastPoint;
    }
}
