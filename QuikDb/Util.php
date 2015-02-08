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
}