<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Setup\Patch\Data;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Zend\Uri\Uri as UriHandler;
use Magento\Framework\Url\ScopeResolverInterface;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Backend\App\Area\FrontNameResolver;

/**
 * Adding base url as allowed downloadable domain.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddDownloadableHostsConfig implements DataPatchInterface
{
    /**
     * @var UriHandler
     */
    private $uriHandler;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var DomainManager
     */
    private $domainManager;

    /**
     * @var array
     */
    private $whitelist = [];

    /**
     * @param UriHandler $uriHandler
     * @param ScopeResolverInterface $scopeResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param DomainManager $domainManager
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        UriHandler $uriHandler,
        ScopeResolverInterface $scopeResolver,
        ScopeConfigInterface $scopeConfig,
        DomainManager $domainManager,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->uriHandler = $uriHandler;
        $this->scopeResolver = $scopeResolver;
        $this->scopeConfig = $scopeConfig;
        $this->domainManager = $domainManager;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->resolveScopeUrls();
        $this->resolveCustomAdminUrl();
        $this->resolveDownloadableLinkUrls();
        $this->resolveDownloadableSampleUrls();

        $this->domainManager->addDomains($this->whitelist);
    }

    /**
     * Add stores and website urls from store scope.
     *
     * @param Store $scope
     * @return void
     */
    private function addStoreAndWebsiteUrlsFromScope(Store $scope): void
    {
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_WEB, false));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_WEB, true));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_LINK, false));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_LINK, true));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_DIRECT_LINK, false));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_DIRECT_LINK, true));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, false));
        $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true));

        try {
            $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_STATIC, false));
            $this->addHost($scope->getBaseUrl(UrlInterface::URL_TYPE_STATIC, true));
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\UnexpectedValueException $e) {
        }

        try {
            $website = $scope->getWebsite();
        } catch (NoSuchEntityException $e) {
            return;
        }

        if ($website) {
            $this->addHost($website->getConfig(Store::XML_PATH_SECURE_BASE_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_UNSECURE_BASE_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_SECURE_BASE_LINK_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_UNSECURE_BASE_LINK_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_SECURE_BASE_MEDIA_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_UNSECURE_BASE_MEDIA_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_SECURE_BASE_STATIC_URL));
            $this->addHost($website->getConfig(Store::XML_PATH_UNSECURE_BASE_STATIC_URL));
        }
    }

    /**
     * Add host to whitelist.
     *
     * @param string|bool $url
     * @return void
     */
    private function addHost($url): void
    {
        if (!is_string($url)) {
            return;
        }

        $host = $this->uriHandler->parse($url)->getHost();
        if ($host && !in_array($host, $this->whitelist)) {
            $this->whitelist[] = $host;
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Add urls from scope.
     *
     * @return void
     */
    private function resolveScopeUrls(): void
    {
        $customStoreScope = $this->scopeResolver->getScope(Custom::CONFIG_SCOPE_ID);
        $storeScopes = $this->scopeResolver->getScopes();
        $storeScopes[] = $customStoreScope;

        foreach ($storeScopes as $scope) {
            $this->addStoreAndWebsiteUrlsFromScope($scope);
        }
    }

    /**
     * Add custom admin url to whitelist.
     *
     * @return void
     */
    private function resolveCustomAdminUrl(): void
    {
        $customAdminUrl = $this->scopeConfig->getValue(
            FrontNameResolver::XML_PATH_CUSTOM_ADMIN_URL,
            ScopeInterface::SCOPE_STORE
        );

        if ($customAdminUrl) {
            $this->addHost($customAdminUrl);
        }
    }

    /**
     * Add downloadable links urls to whitelist.
     *
     * @return void
     */
    private function resolveDownloadableLinkUrls(): void
    {
        if ($this->moduleDataSetup->tableExists('downloadable_link')) {
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from(
                    $this->moduleDataSetup->getTable('downloadable_link'),
                    ['link_url', 'sample_url']
                )
                ->where('link_type = ? OR sample_type = ?', 'url');

            $linkUrls = $this->moduleDataSetup->getConnection()->fetchAll($select);
            foreach ($linkUrls as $url) {
                if (!empty($url['link_url'])) {
                    $this->addHost($url['link_url']);
                }
                if (!empty($url['sample_url'])) {
                    $this->addHost($url['sample_url']);
                }
            }
        }
    }

    /**
     * Add downloadable sample urls to whitelist.
     *
     * @return void
     */
    private function resolveDownloadableSampleUrls(): void
    {
        if ($this->moduleDataSetup->tableExists('downloadable_sample')) {
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from(
                    $this->moduleDataSetup->getTable('downloadable_sample'),
                    ['sample_url']
                )
                ->where('sample_type = ?', 'url');

            $sampleUrls = $this->moduleDataSetup->getConnection()->fetchCol($select);
            foreach ($sampleUrls as $url) {
                if (!empty($url)) {
                    $this->addHost($url);
                }
            }
        }
    }
}
