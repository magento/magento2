<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Interpreter that instantiates object by a class name
 */
class DataObject implements InterpreterInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var string|null
     */
    private $expectedClass;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string|null $expectedClass
     * @param \Magento\Framework\Stdlib\BooleanUtils|null $booleanUtils
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ?string $expectedClass = null,
        ?BooleanUtils $booleanUtils = null
    ) {
        $this->objectManager = $objectManager;
        $this->expectedClass = $expectedClass;
        $this->booleanUtils = $booleanUtils ?? ObjectManager::getInstance()->get(BooleanUtils::class);
    }

    /**
     * @inheritdoc
     */
    public function evaluate(array $data)
    {
        if (!isset($data['value'])) {
            throw new \InvalidArgumentException('Object class name is missing.');
        }

        $shared = isset($data['shared']) ? $this->booleanUtils->toBoolean($data['shared']) : true;
        $className = $data['value'];
        $result = $shared ? $this->objectManager->get($className) : $this->objectManager->create($className);

        if ($this->expectedClass && !$result instanceof $this->expectedClass) {
            throw new \UnexpectedValueException(
                \sprintf('Instance of %s is expected, got %s instead.', $this->expectedClass, \get_class($result))
            );
        }

        return $result;
    }
}
