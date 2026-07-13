<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Arguments;

use Magento\Framework\Data\Argument\Interpreter\Constant;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that returns value of an application argument, retrieving its name from a constant
 */
class ArgumentInterpreter implements InterpreterInterface
{
    /**
     * @var Constant
     */
    private $constInterpreter;

    /**
     * @param Constant $constInterpreter
     */
    public function __construct(Constant $constInterpreter)
    {
        $this->constInterpreter = $constInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return mixed
     */
    public function evaluate(array $data)
    {
        return ['argument' => $this->constInterpreter->evaluate($data)];
    }
}
