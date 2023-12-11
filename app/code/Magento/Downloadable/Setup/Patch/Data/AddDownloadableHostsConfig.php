<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Setup\Patch\Data;

use Laminas\Uri\Uri as UriHandler;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Url\ScopeResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Adding base url as allowed downloadable domain.
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
     * AddDownloadableHostsConfig constructor.
     *
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
     * @inheritDoc
     */
    public function apply()
    {
        $customStoreScope = $this->scopeResolver->getScope(Custom::CONFIG_SCOPE_ID);
        $storeScopes = $this->scopeResolver->getScopes();
        $allStoreScopes = array_merge($storeScopes, [$customStoreScope]);

        foreach ($allStoreScopes as $scope) {
            $this->addStoreAndWebsiteUrlsFromScope($scope);
        }

        $customAdminUrl = $this->scopeConfig->getValue(
            FrontNameResolver::XML_PATH_CUSTOM_ADMIN_URL,
            ScopeInterface::SCOPE_STORE
        );

        if ($customAdminUrl) {
            $this->addHost($customAdminUrl);
        }

        if ($this->moduleDataSetup->tableExists('downloadable_link')) {
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from(
                    $this->moduleDataSetup->getTable('downloadable_link'),
                    ['link_url']
                )
                ->where('link_type = ?', 'url');

            foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $link) {
                $this->addHost($link['link_url']);
            }

            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from(
                    $this->moduleDataSetup->getTable('downloadable_link'),
                    ['sample_url']
                )
                ->where('sample_type = ?', 'url');

            foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $link) {
                $this->addHost($link['sample_url']);
            }
        }

        if ($this->moduleDataSetup->tableExists('downloadable_sample')) {
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from(
                    $this->moduleDataSetup->getTable('downloadable_sample'),
                    ['sample_url']
                )
                ->where('sample_type = ?', 'url');

            foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $link) {
                $this->addHost($link['sample_url']);
            }
        }

        $this->domainManager->addDomains($this->whitelist);

        return $this;
    }

    /**
     * Add stores and website urls from store scope
     *
     * @param Store $scope
     */
    private function addStoreAndWebsiteUrlsFromScope(Store $scope)
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
        } catch (\UnexpectedValueException $e) {} //@codingStandardsIgnoreLine

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
     * Add host to whitelist
     *
     * @param string $url
     */
    private function addHost($url)
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
}
