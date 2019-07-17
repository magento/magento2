<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend\Uri\Uri as UriHandler;
use Magento\Framework\Url\ScopeResolverInterface;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Adding base url as allowed downloadable domain.
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
     * @param DomainManager $domainManager
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        UriHandler $uriHandler,
        ScopeResolverInterface $scopeResolver,
        DomainManager $domainManager,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->uriHandler = $uriHandler;
        $this->scopeResolver = $scopeResolver;
        $this->domainManager = $domainManager;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
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

        foreach ($this->scopeResolver->getScopes() as $scope) {
            $this->addHost($scope->getBaseUrl());
        }

        $this->domainManager->addEnvDomains(array_unique($this->whitelist));
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
