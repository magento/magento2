<?php
/**
 * Application request factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

class RequestFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create request
     *
     * @param array $arguments
     * @return RequestInterface
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(\Magento\Framework\App\RequestInterface::class, $arguments);
    }
}
