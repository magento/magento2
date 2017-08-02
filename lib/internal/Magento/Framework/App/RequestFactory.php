<?php
/**
 * Application request factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Class \Magento\Framework\App\RequestFactory
 *
 * @since 2.0.0
 */
class RequestFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(\Magento\Framework\App\RequestInterface::class, $arguments);
    }
}
