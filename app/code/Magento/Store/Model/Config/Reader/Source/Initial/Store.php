<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for retrieving configuration from initial config by store scope
 * @since 2.1.3
 */
class Store implements SourceInterface
{
    /**
     * @var Initial
     * @since 2.1.3
     */
    private $initialConfig;

    /**
     * @var Website
     * @since 2.1.3
     */
    private $websiteSource;

    /**
     * @var StoreManagerInterface
     * @since 2.1.3
     */
    private $storeManager;

    /**
     * @var Converter
     * @since 2.1.3
     */
    private $converter;

    /**
     * @param Initial $initialConfig
     * @param Website $website
     * @param StoreManagerInterface $storeManager
     * @param Converter $converter
     * @since 2.1.3
     */
    public function __construct(
        Initial $initialConfig,
        Website $website,
        StoreManagerInterface $storeManager,
        Converter $converter
    ) {
        $this->initialConfig = $initialConfig;
        $this->websiteSource = $website;
        $this->storeManager = $storeManager;
        $this->converter = $converter;
    }

    /**
     * Retrieve config by store scope
     *
     * @param string|null $scopeCode
     * @return array
     * @since 2.1.3
     */
    public function get($scopeCode = null)
    {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore($scopeCode);
            return $this->converter->convert(array_replace_recursive(
                $this->websiteSource->get($store->getData('website_code')),
                $this->initialConfig->getData("stores|{$scopeCode}")
            ));
        } catch (\Exception $e) {
            return [];
        }
    }
}
