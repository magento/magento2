<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 */
class Store implements SourceInterface
{
    /**
     * @var ScopedFactory
     */
    private $collectionFactory;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var Website
     */
    private $websiteSource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopedFactory $collectionFactory
     * @param Converter $converter
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteSource
     * @param StoreManagerInterface $storeManager
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
