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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controller for mass opertions with items
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model;

class MassOperations
{
    /**
     * @var \Magento\GoogleShopping\Helper\Data
     */
    protected $_gleShoppingData = null;

    /**
     * @var \Magento\GoogleShopping\Helper\Category|null
     */
    protected $_gleShoppingCategory = null;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Item factory
     *
     * @var \Magento\GoogleShopping\Model\Service\ItemFactory
     */
    protected $_itemFactory;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

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
     * @param \Magento\GoogleShopping\Model\Service\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\AdminNotification\Model\InboxFactory $inboxFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\GoogleShopping\Helper\Data $gleShoppingData
     * @param \Magento\GoogleShopping\Helper\Category $gleShoppingCategory
     * @param array $data
     */
    public function __construct(
        \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory,
        \Magento\GoogleShopping\Model\Service\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\GoogleShopping\Helper\Data $gleShoppingData,
        \Magento\GoogleShopping\Helper\Category $gleShoppingCategory,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_itemFactory = $itemFactory;
        $this->_productFactory = $productFactory;
        $this->_inboxFactory = $inboxFactory;
        $this->_storeManager = $storeManager;
        $this->_gleShoppingData = $gleShoppingData;
        $this->_gleShoppingCategory = $gleShoppingCategory;
        $this->_logger = $logger;
    }

    /**
     * \Zend_Db_Statement_Exception code for "Duplicate unique index" error
     *
     * @var int
     */
    const ERROR_CODE_SQL_UNIQUE_INDEX = 23000;

    /**
     * Whether general error information were added
     *
     * @var bool
     */
    protected $_hasError = false;

    /**
     * Process locking flag
     *
     * @var \Magento\GoogleShopping\Model\Flag
     */
    protected $_flag;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * Set process locking flag.
     *
     * @param \Magento\GoogleShopping\Model\Flag $flag
     * @return \Magento\GoogleShopping\Model\MassOperations
     */
    public function setFlag(\Magento\GoogleShopping\Model\Flag $flag)
    {
        $this->_flag = $flag;
        return $this;
    }

    /**
     * Add product to Google Content.
     *
     * @param array $productIds
     * @param int $storeId
     * @throws \Zend_Gdata_App_CaptchaRequiredException
     * @throws \Magento\Core\Exception
     * @return \Magento\GoogleShopping\Model\MassOperations
     */
    public function addProducts($productIds, $storeId)
    {
        $totalAdded = 0;
        $errors = array();
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $product = $this->_productFactory->create()->setStoreId($storeId)->load($productId);

                    if ($product->getId()) {
                        $this->_itemFactory->create()->insertItem($product)->save();
                        // The product was added successfully
                        $totalAdded++;
                    }
                } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (\Zend_Gdata_App_Exception $e) {
                    $errors[] = $this->_gleShoppingData->parseGdataExceptionMessage($e->getMessage(), $product);
                } catch (\Zend_Db_Statement_Exception $e) {
                    $message = $e->getMessage();
                    if ($e->getCode() == self::ERROR_CODE_SQL_UNIQUE_INDEX) {
                        $message = __("The Google Content item for product '%1' (in '%2' store) already exists.", $product->getName(), $this->_storeManager->getStore($product->getStoreId())->getName());
                    }
                    $errors[] = $message;
                } catch (\Magento\Core\Exception $e) {
                    $errors[] = __('The product "%1" cannot be added to Google Content. %2', $product->getName(), $e->getMessage());
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                    $errors[] = __('The product "%1" hasn\'t been added to Google Content.', $product->getName());
                }
            }
            if (empty($productIds)) {
                return $this;
            }
        }

        if ($totalAdded > 0) {
            $this->_getNotifier()->addNotice(
                __('Products were added to Google Shopping account.'),
                __('A total of %1 product(s) have been added to Google Content.', $totalAdded)
            );
        }

        if (count($errors)) {
            $this->_getNotifier()->addMajor(
                __('Errors happened while adding products to Google Shopping.'),
                $errors
            );
        }

        if ($this->_flag->isExpired()) {
            $this->_getNotifier()->addMajor(
                __('Operation of adding products to Google Shopping expired.'),
                __('Some products may have not been added to Google Shopping bacause of expiration')
            );
        }

        return $this;
    }

    /**
     * Update Google Content items.
     *
     * @param array|\Magento\GoogleShopping\Model\Resource\Item\Collection $items
     * @throws \Zend_Gdata_App_CaptchaRequiredException
     * @throws \Magento\Core\Exception
     * @return \Magento\GoogleShopping\Model\MassOperations
     */
    public function synchronizeItems($items)
    {
        $totalUpdated = 0;
        $totalDeleted = 0;
        $totalFailed = 0;
        $errors = array();

        $itemsCollection = $this->_getItemsCollection($items);

        if ($itemsCollection) {
            if (count($itemsCollection) < 1) {
                return $this;
            }
            foreach ($itemsCollection as $item) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $item->updateItem();
                    $item->save();
                    // The item was updated successfully
                    $totalUpdated++;
                } catch (\Magento\Gdata\Gshopping\HttpException $e) {
                    if (in_array('notfound', $e->getCodes())) {
                        $item->delete();
                        $totalDeleted++;
                    } else {
                        $this->_addGeneralError();
                        $errors[] = $this->_gleShoppingData
                            ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
                        $totalFailed++;
                    }
                } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (\Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $errors[] = $this->_gleShoppingData
                        ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
                    $totalFailed++;
                } catch (\Magento\Core\Exception $e) {
                    $errors[] = __('The item "%1" cannot be updated at Google Content. %2', $item->getProduct()->getName(), $e->getMessage());
                    $totalFailed++;
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                    $errors[] = __('The item "%1" hasn\'t been updated.', $item->getProduct()->getName());
                    $totalFailed++;
                }
            }
        } else {
            return $this;
        }

        $this->_getNotifier()->addNotice(
            __('Product synchronization with Google Shopping completed'),
            __('A total of %1 items(s) have been deleted; a total of %2 items(s) have been updated.', $totalDeleted, $totalUpdated)
        );
        if ($totalFailed > 0 || count($errors)) {
            array_unshift($errors, __("We cannot update %1 items.", $totalFailed));
            $this->_getNotifier()->addMajor(
                __('Errors happened during synchronization with Google Shopping'),
                $errors
            );
        }

        return $this;
    }

    /**
     * Remove Google Content items.
     *
     * @param array|\Magento\GoogleShopping\Model\Resource\Item\Collection $items
     * @throws \Zend_Gdata_App_CaptchaRequiredException
     * @return \Magento\GoogleShopping\Model\MassOperations
     */
    public function deleteItems($items)
    {
        $totalDeleted = 0;
        $itemsCollection = $this->_getItemsCollection($items);
        $errors = array();
        if ($itemsCollection) {
            if (count($itemsCollection) < 1) {
                return $this;
            }
            foreach ($itemsCollection as $item) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $item->deleteItem()->delete();
                    // The item was removed successfully
                    $totalDeleted++;
                } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (\Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $errors[] = $this->_gleShoppingData
                        ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                    $errors[] = __('The item "%1" hasn\'t been deleted.', $item->getProduct()->getName());
                }
            }
        } else {
            return $this;
        }

        if ($totalDeleted > 0) {
            $this->_getNotifier()->addNotice(
                __('Google Shopping item removal process succeded'),
                __('Total of %1 items(s) have been removed from Google Shopping.', $totalDeleted)
            );
        }
        if (count($errors)) {
            $this->_getNotifier()->addMajor(
                __('Errors happened while deleting items from Google Shopping'),
                $errors
            );
        }

        return $this;
    }

    /**
     * Return items collection by IDs
     *
     * @param array|\Magento\GoogleShopping\Model\Resource\Item\Collection $items
     * @throws \Magento\Core\Exception
     * @return null|\Magento\GoogleShopping\Model\Resource\Item\Collection
     */
    protected function _getItemsCollection($items)
    {
        $itemsCollection = null;
        if ($items instanceof \Magento\GoogleShopping\Model\Resource\Item\Collection) {
            $itemsCollection = $items;
        } else if (is_array($items)) {
            $itemsCollection = $this->_collectionFactory->create()->addFieldToFilter('item_id', $items);
        }

        return $itemsCollection;
    }

    /**
     * Retrieve admin notifier
     *
     * @return \Magento\AdminNotification\Model\Inbox
     */
    protected function _getNotifier()
    {
        return $this->_inboxFactory->create();
    }

    /**
     * Provides general error information
     */
    protected function _addGeneralError()
    {
        if (!$this->_hasError) {
            $this->_getNotifier()->addMajor(
                __('Google Shopping Error'),
                $this->_gleShoppingCategory->getMessage()
            );
            $this->_hasError = true;
        }
    }
}
