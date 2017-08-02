<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Cart;

use Magento\Customer\Model\Checkout\ConfigProvider;

/**
 * Class \Magento\Customer\Model\Cart\ConfigPlugin
 *
 * @since 2.0.0
 */
class ConfigPlugin
{
    /**
     * @var ConfigProvider
     * @since 2.0.0
     */
    protected $configProvider;

    /**
     * @param ConfigProvider $configProvider
     * @since 2.0.0
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, array $result)
    {
        return array_merge_recursive($result, $this->configProvider->getConfig());
    }
}
