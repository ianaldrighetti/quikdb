<?php
namespace QuikDb;

/**
 * Class Util
 *
 * @package QuikDb
 */
class Util
{
    /**
     * Indicates whether the specified name is allowed (starts with a character, then alphanumeric and _ allowed).
     *
     * @param $name The name to check.
     * @return bool
     */
    public static function isNameAllowed($name)
    {
        return preg_match('~^[a-z][a-z0-9_]*$~i', $name) !== false;
    }

    /**
     * @param $str
     * @return int
     */
    public static function strlen($str)
    {
        return function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);
    }

    /**
     * @return int
     */
    public static function getFloatSize()
    {
        static $size = null;

        if (!is_null($size))
        {
            return $size;
        }

        $size = strlen(pack('f', 1.1));
        return $size;
    }
}