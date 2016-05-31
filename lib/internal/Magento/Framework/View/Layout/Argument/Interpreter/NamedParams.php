<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter of named parameters
 */
class NamedParams implements InterpreterInterface
{
    /**
     * Interpreter of individual parameter
     *
     * @var InterpreterInterface
     */
    private $paramInterpreter;

    /**
     * @param InterpreterInterface $paramInterpreter
     */
    public function __construct(InterpreterInterface $paramInterpreter)
    {
        $this->paramInterpreter = $paramInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return array
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        $params = isset($data['param']) ? $data['param'] : [];
        if (!is_array($params)) {
            throw new \InvalidArgumentException('Layout argument parameters are expected to be an array.');
        }
        $result = [];
        foreach ($params as $paramKey => $paramData) {
            if (!is_array($paramData)) {
                throw new \InvalidArgumentException('Parameter data of layout argument is expected to be an array.');
            }
            $result[$paramKey] = $this->paramInterpreter->evaluate($paramData);
        }
        return $result;
    }
}
