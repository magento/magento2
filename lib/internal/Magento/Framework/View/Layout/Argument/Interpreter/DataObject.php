<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Interpreter that instantiates object by a class name
 */
class DataObject implements InterpreterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string|null
     */
    private $expectedClass;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string|null $expectedClass
     */
    public function __construct(ObjectManagerInterface $objectManager, $expectedClass = null)
    {
        $this->objectManager = $objectManager;
        $this->expectedClass = $expectedClass;
    }

    /**
     * {@inheritdoc}
     * @return object
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['value'])) {
            throw new \InvalidArgumentException('Object class name is missing.');
        }
        $className = $data['value'];
        $result = $this->objectManager->create($className);
        if ($this->expectedClass && !$result instanceof $this->expectedClass) {
            throw new \UnexpectedValueException(
                sprintf("Instance of %s is expected, got %s instead.", $this->expectedClass, get_class($result))
            );
        }
        return $result;
    }
}
