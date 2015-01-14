<?php

class Magento_Test_Php_Exemplar_CodeMessTest_phpmd_input_parameter_list
{
    /**
     * Method that violates the allowed parameter list length
     */
    public function bar($param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9, $param10)
    {
        return $param1 . $param2 . $param3 . $param4 . $param5 . $param6 . $param7 . $param8 . $param9 . $param10;
    }
}
