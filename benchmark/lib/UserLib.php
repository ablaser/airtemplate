<?php

class UserLib
{
    /**
     * Truncates a string to a word boundary if it is longer than $length,
     * otherwise return the string unchanged.
     *
     * @param string $value  The input string
     * @param int    $length Maximum string length
     * @param string $trail  A string to append to the truncated value
     *
     * @return string
     */
    public static function truncateWords($value, $length = 80, $trail = ' &hellip;')
    {
        if (strlen($value) <= $length) {
            return $value;
        }
        $v = substr($value, 0, strrpos(substr($value, 0, $length), ' '));
        return ($v != ''
            ? htmlspecialchars($v)
            : htmlspecialchars(substr($value, 0, $length))) . $trail;
    }
}