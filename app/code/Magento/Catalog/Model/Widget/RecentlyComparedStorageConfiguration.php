<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Widget;

use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configurate all storages that needed for recently viewed widgets
 * @since 2.2.0
 */
class RecentlyComparedStorageConfiguration implements FrontendStorageConfigurationInterface
{
    /** Recently Viewed lifetime */
    const XML_LIFETIME_PATH = "catalog/recently_products/recently_compared_lifetime";

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * RecentlyViewedStorageConfiguration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Parse lifetime of recently compared products in widget
     *
     * @inheritdoc
     * @since 2.2.0
     */
    public function get()
    {
        return [
            'lifetime' => $this->scopeConfig->getValue(self::XML_LIFETIME_PATH)
        ];
    }
}
