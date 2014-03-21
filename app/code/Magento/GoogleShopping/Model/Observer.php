<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Message\ManagerInterface
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
     * Inbox factory
     *
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $_inboxFactory;

    /**
     * Collection factory
     *
     * @var \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory
     * @param \Magento\GoogleShopping\Model\MassOperationsFactory $operationsFactory
     * @param \Magento\AdminNotification\Model\InboxFactory $inboxFactory
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Message\ManagerInterface $messageManager
     * @param \Magento\GoogleShopping\Model\Flag $flag
     */
    public function __construct(
        \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory,
        \Magento\GoogleShopping\Model\MassOperationsFactory $operationsFactory,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Message\ManagerInterface $messageManager,
        \Magento\GoogleShopping\Model\Flag $flag
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_operationsFactory = $operationsFactory;
        $this->_inboxFactory = $inboxFactory;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->messageManager = $messageManager;
        $this->_flag = $flag;
    }

    /**
     * Update product item in Google Content
     *
     * @param \Magento\Object $observer
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
     * @param \Magento\Object $observer
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
            if (!$this->_coreStoreConfig->getConfigFlag('google/googleshopping/observed', $item->getStoreId())) {
                $items->removeItemByKey($item->getId());
            }
        }

        return $items;
    }

    /**
     * Check if synchronize process is finished and generate notification message
     *
     * @param  \Magento\Event\Observer $observer
     * @return $this
     */
    public function checkSynchronizationOperations(\Magento\Event\Observer $observer)
    {
        $this->_flag->loadSelf();
        if ($this->_flag->isExpired()) {
            $this->_inboxFactory->create()->addMajor(
                __('Google Shopping operation has expired.'),
                __('One or more google shopping synchronization operations failed because of timeout.')
            );
            $this->_flag->unlock();
        }
        return $this;
    }
}
