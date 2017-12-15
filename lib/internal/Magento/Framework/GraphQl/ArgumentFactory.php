<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Argument\ArgumentValueInterface;

/**
 * Creates arguments represented by ArgumentInterface
 */
class ArgumentFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a field argument from map
     *
     * @param string $argumentName
     * @param bool|float|int|ArgumentValueInterface|string $argumentValue
     * @return ArgumentInterface
     */
    public function create($argumentName, $argumentValue)
    {
        return $this->objectManager->create(
            ArgumentInterface::class,
            [
                'name' => $argumentName,
                'value' => $argumentValue
            ]
        );
    }
}
