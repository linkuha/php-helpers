<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 29.12.2018
 * Time: 16:15
 */

namespace SimpleLibs;

/**
 * Class ConfigHelper to store the configuration in an array
 *
 * Example to use:
 *
 * ConfigHelper::write('conf1.txt', array( 'setting_1' => 'foo' ));
 * $config = ConfigHelper::read('conf1.txt');
 * $config['setting_1'] = 'bar';
 * $config['setting_2'] = 'baz';
 * ConfigHelper::write('conf1.txt', $config);
 *
 * @package helpers
 */
final class ConfigHelper
{
    public static function read($filePath)
    {
        $config = include_once $filePath;
        return $config;
    }
    public static function write($filePath, array $config)
    {
        $config = var_export($config, true);
        file_put_contents($filePath, "<?php return $config ;");
    }
}
