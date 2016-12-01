<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for retrieving configuration from initial config by store scope
 */
class Store implements SourceInterface
{
    /**
     * @var Initial
     */
    private $initialConfig;

    /**
     * @var Website
     */
    private $websiteSource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param Initial $initialConfig
     * @param Website $website
     * @param StoreManagerInterface $storeManager
     * @param Converter $converter
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
