<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;

/**
 * Class for retrieving configuration from DB by store scope
 * @since 2.2.0
 */
class Store implements SourceInterface
{
    /**
     * @var ScopedFactory
     * @since 2.2.0
     */
    private $collectionFactory;

    /**
     * @var Converter
     * @since 2.2.0
     */
    private $converter;

    /**
     * @var WebsiteFactory
     * @since 2.2.0
     */
    private $websiteFactory;

    /**
     * @var Website
     * @since 2.2.0
     */
    private $websiteSource;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @param ScopedFactory $collectionFactory
     * @param Converter $converter
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteSource
     * @param StoreManagerInterface $storeManager
     * @since 2.2.0
     */
    public function __construct(
        ScopedFactory $collectionFactory,
        Converter $converter,
        WebsiteFactory $websiteFactory,
        Website $websiteSource,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter;
        $this->websiteFactory = $websiteFactory;
        $this->websiteSource = $websiteSource;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve config by store scope
     *
     * @param string|null $scopeCode
     * @return array
     * @since 2.2.0
     */
    public function get($scopeCode = null)
    {
        try {
            $store = $this->storeManager->getStore($scopeCode);
            $collection = $this->collectionFactory->create(
                ['scope' => ScopeInterface::SCOPE_STORES, 'scopeId' => $store->getId()]
            );

            $config = [];
            foreach ($collection as $item) {
                $config[$item->getPath()] = $item->getValue();
            }
            return $this->converter->convert(array_replace_recursive(
                $this->websiteSource->get($store->getWebsiteId()),
                $this->converter->convert($config)
            ));
        } catch (\DomainException $e) {
            return [];
        }
    }
}
