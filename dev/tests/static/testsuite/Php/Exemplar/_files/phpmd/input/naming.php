<?php

class Foo
{
    const nonUppercaseName = false;

    /**
     * Too short field name
     * @var string
     */
    protected $_a;

    protected $_tooLongPropertyName1;

    /**
     * Legacy PHP4 style constructor
     */
    public function Foo() {}

    public function bar($a1 = 'too short parameter name', $tooLongParameterName2 = '')
    {
        $a2 = 'too short local variable name';
        $tooLongLocalVariable3 = '';
        return $a1 . $a2 . $tooLongParameterName2 . $tooLongLocalVariable3;
    }

    /**
     * Too short method name
     */
    protected function _x() {}

    /**
     * Getter that returns boolean value should be named 'is...()' or 'has...()'
     * @return bool
     */
    public function getBoolValue()
    {
        return true;
    }
}
