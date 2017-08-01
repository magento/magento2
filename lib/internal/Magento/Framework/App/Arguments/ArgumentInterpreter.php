<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments;

use Magento\Framework\Data\Argument\Interpreter\Constant;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that returns value of an application argument, retrieving its name from a constant
 * @since 2.0.0
 */
class ArgumentInterpreter implements InterpreterInterface
{
    /**
     * @var Constant
     * @since 2.0.0
     */
    private $constInterpreter;

    /**
     * @param Constant $constInterpreter
     * @since 2.0.0
     */
    public function __construct(Constant $constInterpreter)
    {
        $this->constInterpreter = $constInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return mixed
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        return ['argument' => $this->constInterpreter->evaluate($data)];
    }
}
