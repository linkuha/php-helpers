<?php
/**
 * Created by PhpStorm.
 * User: linkuha
 * Date: 19.11.2018
 * Time: 19:16
 */

namespace SimpleLibs;

class RetryHelper
{
    public static function doRetryWrap($triesCount, $function, $errCallback, $exceptions = [\Exception::class], $pause = 5)
    {
        if (! is_callable($function)) {
            throw new \InvalidArgumentException("Is not callable function passed.");
        }
        if (! is_numeric($triesCount)) {
            throw new \InvalidArgumentException("Count must be a digit.");
        }
        $successFlag = false;
        $errClass = \Exception::class;
        $errMsg = "Error";
        do {
            try {
                $function();
                $successFlag = true;
                return true;
            } catch (\Exception $e) {
                $errClass = get_class($e);
                $errMsg = $e->getMessage();
//                file_put_contents("/tmp/retry_helper.log", $errClass . PHP_EOL, FILE_APPEND);
//                $errClass = (new \ReflectionClass($e))->getShortName();
                if (in_array($errClass, $exceptions)) {
                    $triesCount--;
                    $errCallback();
                    sleep($pause);
                    continue;
                } else {
                    break;
                }
            }
        } while (! $successFlag && $triesCount);
        throw new $errClass($errMsg);
    }
}
