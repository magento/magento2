<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @since 2.1.0
 */
class ModifierFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.1.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create model
     *
     * @param string $className
     * @param array $data
     * @return ModifierInterface
     * @throws \InvalidArgumentException
     * @since 2.1.0
     */
    public function create($className, array $data = [])
    {
        $model = $this->objectManager->create($className, $data);

        if (!$model instanceof ModifierInterface) {
            throw new \InvalidArgumentException(
                'Type "' . $className . '" is not instance on ' . ModifierInterface::class
            );
        }

        return $model;
    }
}
