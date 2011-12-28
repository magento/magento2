<?php

class Foo
{
    public function terminateApplication($exitCode = 0)
    {
        exit($exitCode);
    }

    public function evaluateExpression($expression)
    {
        return eval($expression);
    }
}
