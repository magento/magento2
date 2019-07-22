<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Setup;

use Zend\Uri\Uri as UriHandler;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Url\ScopeResolverInterface;

/**
 * @codeCoverageIgnore
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
        ScopeResolverInterface $scopeResolver,
        DomainManager $domainManager
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->uriHandler = $uriHandler;
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

    private function addDownloadableHostsToConfig(ModuleDataSetupInterface $setup)
    {
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
     * Add host to whitelist
     *
     * @param string $url
     */
    private function addHost($url)
    {
        $host = $this->uriHandler->parse($url)->getHost();
        if ($host && !in_array($host, $this->whitelist)) {
            $this->whitelist[] = $host;
        }
    }
}
