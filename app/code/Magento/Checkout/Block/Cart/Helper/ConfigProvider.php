<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart\Helper;

class ConfigProvider
{
    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    private $configProvider;
    
    /**
     * Config constructor.
     *
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     */
    public function __construct(
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }
    
    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }
    
    /**
     * @return bool|string
     */
    public function getSerializedCheckoutConfig()
    {
        return json_encode($this->getCheckoutConfig(), JSON_HEX_TAG);
    }
}
