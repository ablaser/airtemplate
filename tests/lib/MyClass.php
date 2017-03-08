<?php

namespace MyNamespace\MySubNamespace;

class MyClass
{
    public static function myStaticMethod($value) {
        return __FUNCTION__ . ': ' . $value;
    }
}
