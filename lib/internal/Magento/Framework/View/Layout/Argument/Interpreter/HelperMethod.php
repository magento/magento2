<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Interpreter that returns invocation result of a helper method
 * @since 2.0.0
 */
class HelperMethod implements InterpreterInterface
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var NamedParams
     * @since 2.0.0
     */
    private $paramsInterpreter;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param NamedParams $paramsInterpreter
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager, NamedParams $paramsInterpreter)
    {
        $this->objectManager = $objectManager;
        $this->paramsInterpreter = $paramsInterpreter;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        if (!isset($data['helper']) || substr_count($data['helper'], '::') != 1) {
            throw new \InvalidArgumentException('Helper method name in format "\Class\Name::methodName" is expected.');
        }
        $helperMethod = $data['helper'];
        list($helperClass, $methodName) = explode('::', $helperMethod, 2);
        if (!method_exists($helperClass, $methodName)) {
            throw new \InvalidArgumentException("Helper method '{$helperMethod}' does not exist.");
        }
        $methodParams = $this->paramsInterpreter->evaluate($data);
        $methodParams = array_values($methodParams);
        // Use positional argument binding instead of named binding
        $helperInstance = $this->objectManager->get($helperClass);
        return call_user_func_array([$helperInstance, $methodName], $methodParams);
    }
}
