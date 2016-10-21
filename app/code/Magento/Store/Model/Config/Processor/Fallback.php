<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Store\App\Config\Type\Scopes;

/**
 * Fallback throguh different scopes and merge them
 *
 * @package Magento\Store\Model\Config\Processor
 */
class Fallback implements PostProcessorInterface
{
    /**
     * @var Scopes
     */
    private $scopes;

    /**
     * Fallback constructor.
     * @param Scopes $scopes
     */
    public function __construct(Scopes $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @inheritdoc
     */
    public function process(array $data)
    {
        $defaultConfig = isset($data['default']) ? $data['default'] : [];
        $result = [
            'default' => $defaultConfig,
            'websites' => [],
            'stores' => []
        ];

        $websitesConfig = isset($data['websites']) ? $data['websites'] : [];
        $result['websites'] = $this->prepareWebsitesConfig($defaultConfig, $websitesConfig);

        $storesConfig = isset($data['stores']) ? $data['stores'] : [];
        $result['stores'] = $this->prepareStoresConfig($defaultConfig, $websitesConfig, $storesConfig);

        return $result;
    }

    /**
     * Prepare website data from Config/Type/Scopes
     *
     * @param array $defaultConfig
     * @param array $websitesConfig
     * @return array
     */
    private function prepareWebsitesConfig(array $defaultConfig, array $websitesConfig)
    {
        $result = [];
        foreach ($this->scopes->get('websites') as $websiteData) {
            $code = $websiteData['code'];
            $id = $websiteData['website_id'];
            $websiteConfig = isset($websitesConfig[$code]) ? $websitesConfig[$code] : [];
            $result[$code] = array_replace_recursive($defaultConfig, $websiteConfig);
            $result[$id] = $result[$code];
        }
        return $result;
    }

    /**
     * Prepare stores data from Config/Type/Scopes
     *
     * @param array $defaultConfig
     * @param array $websitesConfig
     * @param array $storesConfig
     * @return array
     */
    private function prepareStoresConfig(array $defaultConfig, array $websitesConfig, array $storesConfig)
    {
        $result = [];
        foreach ($this->scopes->get('stores') as $storeData) {
            $code = $storeData['code'];
            $id = $storeData['store_id'];
            $websiteConfig = [];
            if (isset($storeData['website_id'])) {
                $websiteConfig = $this->getWebsiteConfig($websitesConfig, $storeData['website_id']);
            }
            $storeConfig = isset($storesConfig[$code]) ? $storesConfig[$code] : [];
            $result[$code] = array_replace_recursive($defaultConfig, $websiteConfig, $storeConfig);
            $result[$id] = $result[$code];
        }
        return $result;
    }

    /**
     * Retrieve Website Config
     *
     * @param array $websites
     * @param int $id
     * @return array
     */
    private function getWebsiteConfig(array $websites, $id)
    {
        foreach ($this->scopes->get('websites') as $websiteData) {
            if ($websiteData['website_id'] == $id) {
                $code = $websiteData['code'];
                return isset($websites[$code]) ? $websites[$code] : [];
            }
        }
        return [];
    }
}
