<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Setup;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Zend\Uri\Uri as UriHandler;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Url\ScopeResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
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
     * @var DomainManager
     */
    private $domainManager;

    /**
     * @var array
     */
    private $whitelist = [];

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        UriHandler $uriHandler,
        ScopeConfigInterface $scopeConfig,
        ScopeResolverInterface $scopeResolver,
        DomainManager $domainManager
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->uriHandler = $uriHandler;
        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
        $this->domainManager = $domainManager;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            // remove default value
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'links_exist',
                'default_value',
                null
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->addDownloadableHostsToConfig($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add existing Downloadable Hosts to env.php
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function addDownloadableHostsToConfig(ModuleDataSetupInterface $setup)
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

        if ($setup->tableExists('downloadable_link')) {
            $select = $setup->getConnection()
                ->select()
                ->from(
                    $setup->getTable('downloadable_link'),
                    ['link_url']
                )
                ->where('link_type = ?', 'url');

            foreach ($setup->getConnection()->fetchAll($select) as $link) {
                $this->addHost($link['link_url']);
            }

            $select = $setup->getConnection()
                ->select()
                ->from(
                    $setup->getTable('downloadable_link'),
                    ['sample_url']
                )
                ->where('sample_type = ?', 'url');

            foreach ($setup->getConnection()->fetchAll($select) as $link) {
                $this->addHost($link['sample_url']);
            }
        }

        if ($setup->tableExists('downloadable_sample')) {
            $select = $setup->getConnection()
                ->select()
                ->from(
                    $setup->getTable('downloadable_sample'),
                    ['sample_url']
                )
                ->where('sample_type = ?', 'url');

            foreach ($setup->getConnection()->fetchAll($select) as $link) {
                $this->addHost($link['sample_url']);
            }
        }

        foreach ($this->scopeResolver->getScopes() as $scope) {
            $this->addHost($scope->getBaseUrl());
        }

        $this->domainManager->addDomains($this->whitelist);
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
}
