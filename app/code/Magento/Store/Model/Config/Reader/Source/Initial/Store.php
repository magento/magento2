<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Initial;

use Exception;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\Store as ModelStore;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for retrieving configuration from initial config by store scope
 */
class Store implements SourceInterface
{
    /**
     * @var Website
     */
    private $websiteSource;

    /**
     * @param Initial $initialConfig
     * @param Website $website
     * @param StoreManagerInterface $storeManager
     * @param Converter $converter
     */
    public function __construct(
        private readonly Initial $initialConfig,
        Website $website,
        private readonly StoreManagerInterface $storeManager,
        private readonly Converter $converter
    ) {
        $this->websiteSource = $website;
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
            /** @var ModelStore $store */
            $store = $this->storeManager->getStore($scopeCode);
            return $this->converter->convert(array_replace_recursive(
                $this->websiteSource->get($store->getData('website_code')),
                $this->initialConfig->getData("stores|{$scopeCode}")
            ));
        } catch (Exception $e) {
            return [];
        }
    }
}
