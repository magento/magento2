<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader;

use Magento\Framework\Exception\NoSuchEntityException;

class Store implements \Magento\Framework\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @var \Magento\Store\Model\Config\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Store\Model\Resource\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     * @param \Magento\Store\Model\Config\Converter $converter
     * @param \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\Config\ScopePool $scopePool,
        \Magento\Store\Model\Config\Converter $converter,
        \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_scopePool = $scopePool;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeFactory = $storeFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Read configuration by code
     *
     * @param string $code
     * @return array
     * @throws NoSuchEntityException
     */
    public function read($code = null)
    {
        if (empty($code)) {
            $store = $this->_storeManager->getStore();
        } elseif (($code == \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT)) {
            $store = $this->_storeManager->getDefaultStoreView();
        } else {
            $store = $this->_storeFactory->create();
            $store->load($code);
        }

        if (!($store && $store->getCode())) {
            throw NoSuchEntityException::singleField('storeCode', $code);
        }
        $websiteConfig = $this->_scopePool->getScope(
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $store->getWebsite()->getCode()
        )->getSource();
        $config = array_replace_recursive($websiteConfig, $this->_initialConfig->getData("stores|{$code}"));

        $collection = $this->_collectionFactory->create(
            ['scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORES, 'scopeId' => $store->getId()]
        );
        $dbStoreConfig = [];
        foreach ($collection as $item) {
            $dbStoreConfig[$item->getPath()] = $item->getValue();
        }
        $config = $this->_converter->convert($dbStoreConfig, $config);
        return $config;
    }
}
