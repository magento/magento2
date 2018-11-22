<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Cart;

/**
 * Provides the checkout configuration.
 */
class ConfigProvider implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    private $configProvider;
    
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;
    
    /**
     * Config constructor.
     *
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }
    
    /**
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }
    
    /**
     * Retrieve checkout serialized configuration
     *
     * @return bool|string
     */
    public function getSerializedCheckoutConfig()
    {
        return $this->serializer->serialize($this->getCheckoutConfig());
    }
}
