<?php

class Magento_Test_Php_Exemplar_CodeMessTest_phpmd_input_prohibited_statement
{
    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function terminateApplication($exitCode = 0)
    {
        exit($exitCode);
    }

    /**
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    public function evaluateExpression($expression)
    {
        return eval($expression);
    }
}
