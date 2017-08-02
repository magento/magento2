<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Class \Magento\Framework\Mview\View\AbstractFactory
 *
 * @since 2.0.0
 */
abstract class AbstractFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Instance name
     */
    const INSTANCE_NAME = '';

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return CollectionInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $data);
    }
}
