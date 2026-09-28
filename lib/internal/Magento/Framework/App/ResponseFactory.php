<?php
/**
 * Application response factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

class ResponseFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Framework\App\ResponseInterface::class, $arguments);
    }
}
