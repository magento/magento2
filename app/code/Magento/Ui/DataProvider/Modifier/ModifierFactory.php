<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 */
class ModifierFactory
{
    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * Create model
     *
     * @param string $className
     * @param array $data
     * @return ModifierInterface
     * @throws InvalidArgumentException
     */
    public function create($className, array $data = [])
    {
        $model = $this->objectManager->create($className, $data);

        if (!$model instanceof ModifierInterface) {
            throw new InvalidArgumentException(
                'Type "' . $className . '" is not instance on ' . ModifierInterface::class
            );
        }

        return $model;
    }
}
