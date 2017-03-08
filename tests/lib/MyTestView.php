<?php

class MyTestView
{
    public function myTestMethod($value, $field, $data)
    {
        return __FUNCTION__ . ': ' . $value;
    }

    public static function myTestStatic($value, $field, $data)
    {
        return __FUNCTION__ . ': ' . $value;
    }
}
