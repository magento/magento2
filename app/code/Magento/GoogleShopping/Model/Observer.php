<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

/**
 * Google Shopping Observer
 */
class Observer
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Admin session
     *
     * @var \Magento\GoogleShopping\Model\Flag
     */
    protected $_flag;

    /**
     * Mass operations factory
     *
     * @var \Magento\GoogleShopping\Model\MassOperationsFactory
     */
    protected $_operationsFactory;

    /**
     * Notifier
     *
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    protected $_notifier;

    /**
     * Collection factory
     *
     * @var \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory
     * @param \Magento\GoogleShopping\Model\MassOperationsFactory $operationsFactory
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\GoogleShopping\Model\Flag $flag
     */
    public function __construct(
        \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory,
        \Magento\GoogleShopping\Model\MassOperationsFactory $operationsFactory,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\GoogleShopping\Model\Flag $flag
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_operationsFactory = $operationsFactory;
        $this->_notifier = $notifier;
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->_flag = $flag;
    }

    /**
     * Update product item in Google Content
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function saveProductItem($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $items = $this->_getItemsCollection($product);

        try {
            $this->_operationsFactory->create()->synchronizeItems($items);
        } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
            $this->messageManager->addError('Cannot update Google Content Item. Google requires CAPTCHA.');
        }

        return $this;
    }

    /**
     * Delete product item from Google Content
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function deleteProductItem($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $items = $this->_getItemsCollection($product);

        try {
            $this->_operationsFactory->create()->deleteItems($items);
        } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
            $this->messageManager->addError('Cannot delete Google Content Item. Google requires CAPTCHA.');
        }

        return $this;
    }

    /**
     * Get items which are available for update/delete when product is saved
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\GoogleShopping\Model\Resource\Item\Collection
     */
    protected function _getItemsCollection($product)
    {
        $items = $this->_collectionFactory->create()->addProductFilterId($product->getId());
        if ($product->getStoreId()) {
            $items->addStoreFilter($product->getStoreId());
        }

        foreach ($items as $item) {
            $flag = $this->_scopeConfig->isSetFlag(
                'google/googleshopping/observed',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $item->getStoreId()
            );
            if (!$flag) {
                $items->removeItemByKey($item->getId());
            }
        }

        return $items;
    }

    /**
     * Check if synchronize process is finished and generate notification message
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function checkSynchronizationOperations(\Magento\Framework\Event\Observer $observer)
    {
        $this->_flag->loadSelf();
        if ($this->_flag->isExpired()) {
            $this->_notifier->addMajor(
                __('Google Shopping operation has expired.'),
                __('One or more google shopping synchronization operations failed because of timeout.')
            );
            $this->_flag->unlock();
        }
        return $this;
    }
}
