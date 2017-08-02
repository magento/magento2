<?php
/**
 * Application response factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Class \Magento\Framework\App\ResponseFactory
 *
 * @since 2.0.0
 */
class ResponseFactory
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
     * Create response
     *
     * @param array $arguments
     * @return ResponseInterface
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Framework\App\ResponseInterface::class, $arguments);
    }
}
