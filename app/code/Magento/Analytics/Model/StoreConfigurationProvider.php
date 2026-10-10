<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides config data report
 */
class StoreConfigurationProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]
     */
    private $configPaths;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string[] $configPaths
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        array $configPaths
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configPaths = $configPaths;
        $this->storeManager = $storeManager;
    }

    /**
     * Generates report using config paths from di.xml
     *
     * For each website and store
     *
     * @return \IteratorIterator
     */
    public function getReport()
    {
        $reportsPerScope = [$this->generateReportForScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0)];

        /** @var WebsiteInterface $website */
        foreach ($this->storeManager->getWebsites() as $website) {
            $reportsPerScope[] = $this->generateReportForScope(ScopeInterface::SCOPE_WEBSITES, $website->getId());
        }

        /** @var StoreInterface $store */
        foreach ($this->storeManager->getStores() as $store) {
            $reportsPerScope[] = $this->generateReportForScope(ScopeInterface::SCOPE_STORES, $store->getId());
        }

        // Each scope used to be prepended to the report, so the most specific scope comes first
        $configReport = array_merge(...array_reverse($reportsPerScope));

        return new \IteratorIterator(new \ArrayIterator($configReport));
    }

    /**
     * Creates report from config for scope type and scope id.
     *
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    private function generateReportForScope($scope, $scopeId)
    {
        $report = [];
        foreach ($this->configPaths as $configPath) {
            $report[] = [
                "config_path" => $configPath,
                "scope" => $scope,
                "scope_id" => $scopeId,
                "value" => $this->scopeConfig->getValue(
                    $configPath,
                    $scope,
                    $scopeId
                )
            ];
        }
        return $report;
    }
}
