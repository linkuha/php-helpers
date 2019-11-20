<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 05.06.2019
 * Time: 14:34
 */

namespace SimpleLibs;


class VersionHelper
{
    public static function simpleToSemantic($shortVersion)
    {
        $version = rtrim("$shortVersion", ".");
        $version = preg_replace("/[^\d.]/", "", $version);
        $parts = explode(".", $version);
        foreach ($parts as &$part) {
            $part = intval($part);
        }
        $version = join(".", $parts);
        if (count($parts) < 3) {
            $version .= ".0";
            if (count($parts) < 2) {
                $version .= ".0";
            }
        }
        return $version;
    }

    /**
     * TODO create adapter?
     *
     * @param $version
     * @return string
     */
    public static function increase($version)
    {
        $sv = new \PHLAK\SemVer\Version($version);
        if ($sv->patch + 1 < 100) {
            $sv->incrementPatch();
            return (string) $sv;
        } else {
            $sv->setPatch(0);
            if ($sv->minor + 1 < 10) {
                $sv->incrementMinor();
                return (string) $sv;
            } else {
                $sv->setMinor(0);
                $sv->incrementMajor();
                return (string) $sv;
            }
        }
    }
}
