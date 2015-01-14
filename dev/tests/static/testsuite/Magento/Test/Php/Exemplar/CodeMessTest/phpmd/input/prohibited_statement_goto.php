<?php

class Magento_Test_Php_Exemplar_CodeMessTest_phpmd_input_prohibited_statement_goto
{
    public function loopArrayCallback(array $array, $callback)
    {
        $index = 0;
        while (true) {
            if ($index >= count($array)) {
                goto end;
            }
            $callback($array[$index]);
            $index++;
        }
        end:
    }
}
