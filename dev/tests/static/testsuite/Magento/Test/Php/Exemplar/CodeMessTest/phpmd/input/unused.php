<?php

class Magento_Test_Php_Exemplar_CodeMessTest_phpmd_input_unused
{
    private $_unusedField;

    private function _unusedMethod() {}

    public function bar($unusedParameter)
    {
        $unusedLocalVariable = 'unused value';
    }
}
