<?php

class Foo
{
    private $_unusedField;

    private function _unusedMethod() {}

    public function bar($unusedParameter)
    {
        $unusedLocalVariable = 'unused value';
    }
}
