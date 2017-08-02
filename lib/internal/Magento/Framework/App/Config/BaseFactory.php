<?php
/**
 * Base config model factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Class \Magento\Framework\App\Config\BaseFactory
 *
 * @since 2.0.0
 */
class BaseFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create config model
     *
     * @param string|\Magento\Framework\Simplexml\Element $sourceData
     * @return \Magento\Framework\App\Config\Base
     * @since 2.0.0
     */
    public function create($sourceData = null)
    {
        return $this->_objectManager->create(\Magento\Framework\App\Config\Base::class, ['sourceData' => $sourceData]);
    }
}
