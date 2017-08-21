<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Class \Magento\Framework\Mview\View\AbstractFactory
 *
 */
abstract class AbstractFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name
     */
    const INSTANCE_NAME = '';

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $data);
    }
}
