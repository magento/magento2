<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

use Magento\Framework\Model\Exception as CoreException;
use Magento\GoogleShopping\Model\Resource\Item\Collection as ItemCollection;

/**
 * Controller for mass opertions with items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class MassOperations
{
    /**
     * GoogleShopping data
     *
     * @var \Magento\GoogleShopping\Helper\Data
     */
    protected $_gleShoppingData = null;

    /**
     * GoogleShopping category
     *
     * @var \Magento\GoogleShopping\Helper\Category|null
     */
    protected $_gleShoppingCategory = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Item factory
     *
     * @var \Magento\GoogleShopping\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

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
     * @param \Magento\GoogleShopping\Model\ItemFactory $itemFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\GoogleShopping\Helper\Data $gleShoppingData
     * @param \Magento\GoogleShopping\Helper\Category $gleShoppingCategory
     * @param array $data
     */
    public function __construct(
        \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory,
        \Magento\GoogleShopping\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\GoogleShopping\Helper\Data $gleShoppingData,
        \Magento\GoogleShopping\Helper\Category $gleShoppingCategory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_itemFactory = $itemFactory;
        $this->productRepository = $productRepository;
        $this->_notifier = $notifier;
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
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Set process locking flag.
     *
     * @param \Magento\GoogleShopping\Model\Flag $flag
     * @return $this
     */
    public function setFlag(\Magento\GoogleShopping\Model\Flag $flag)
    {
        $this->_flag = $flag;
        return $this;
    }

    /**
     * Add product to Google Content.
     *
     * @param int[] $productIds
     * @param int $storeId
     * @return $this
     * @throws \Exception|\Zend_Gdata_App_CaptchaRequiredException
     */
    public function addProducts($productIds, $storeId)
    {
        $totalAdded = 0;
        $errors = [];
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $product = $this->productRepository->getById($productId, false, $storeId);
                    $item = $this->_itemFactory->create();
                    $item->insertItem($product)->save();
                    // The product was added successfully
                    $totalAdded++;
                } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
                } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (\Zend_Gdata_App_Exception $e) {
                    $errors[] = $this->_gleShoppingData->parseGdataExceptionMessage($e->getMessage(), $product);
                } catch (\Zend_Db_Statement_Exception $e) {
                    $message = $e->getMessage();
                    if ($e->getCode() == self::ERROR_CODE_SQL_UNIQUE_INDEX) {
                        $message = __(
                            "The Google Content item for product '%1' (in '%2' store) already exists.",
                            $product->getName(),
                            $this->_storeManager->getStore($product->getStoreId())->getName()
                        );
                    }
                    $errors[] = $message;
                } catch (CoreException $e) {
                    $errors[] = __(
                        'The product "%1" cannot be added to Google Content. %2',
                        $product->getName(),
                        $e->getMessage()
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    $errors[] = __('The product "%1" hasn\'t been added to Google Content.', $product->getName());
                }
            }
            if (empty($productIds)) {
                return $this;
            }
        }

        if ($totalAdded > 0) {
            $this->_notifier->addNotice(
                __('Products were added to Google Shopping account.'),
                __('A total of %1 product(s) have been added to Google Content.', $totalAdded)
            );
        }

        if (count($errors)) {
            $this->_notifier->addMajor(__('Errors happened while adding products to Google Shopping.'), $errors);
        }

        if ($this->_flag->isExpired()) {
            $this->_notifier->addMajor(
                __('Operation of adding products to Google Shopping expired.'),
                __('Some products may have not been added to Google Shopping bacause of expiration')
            );
        }

        return $this;
    }

    /**
     * Update Google Content items.
     *
     * @param int[]|ItemCollection $items
     * @return $this
     * @throws \Exception|\Zend_Gdata_App_CaptchaRequiredException
     */
    public function synchronizeItems($items)
    {
        $totalUpdated = 0;
        $totalDeleted = 0;
        $totalFailed = 0;
        $errors = [];

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
                } catch (\Magento\Framework\Gdata\Gshopping\HttpException $e) {
                    if (in_array('notfound', $e->getCodes())) {
                        $item->delete();
                        $totalDeleted++;
                    } else {
                        $this->_addGeneralError();
                        $errors[] = $this->_gleShoppingData->parseGdataExceptionMessage(
                            $e->getMessage(),
                            $item->getProduct()
                        );
                        $totalFailed++;
                    }
                } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (\Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $errors[] = $this->_gleShoppingData->parseGdataExceptionMessage(
                        $e->getMessage(),
                        $item->getProduct()
                    );
                    $totalFailed++;
                } catch (CoreException $e) {
                    $errors[] = __(
                        'The item "%1" cannot be updated at Google Content. %2',
                        $item->getProduct()->getName(),
                        $e->getMessage()
                    );
                    $totalFailed++;
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    $errors[] = __('The item "%1" hasn\'t been updated.', $item->getProduct()->getName());
                    $totalFailed++;
                }
            }
        } else {
            return $this;
        }

        $this->_notifier->addNotice(
            __('Product synchronization with Google Shopping completed'),
            __(
                'A total of %1 items(s) have been deleted; a total of %2 items(s) have been updated.',
                $totalDeleted,
                $totalUpdated
            )
        );
        if ($totalFailed > 0 || count($errors)) {
            array_unshift($errors, __("We cannot update %1 items.", $totalFailed));
            $this->_notifier->addMajor(
                __('Errors happened during synchronization with Google Shopping'),
                $errors
            );
        }

        return $this;
    }

    /**
     * Remove Google Content items.
     *
     * @param int[]|ItemCollection $items
     * @return $this
     * @throws \Exception|\Zend_Gdata_App_CaptchaRequiredException
     */
    public function deleteItems($items)
    {
        $totalDeleted = 0;
        $itemsCollection = $this->_getItemsCollection($items);
        $errors = [];
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
                    $errors[] = $this->_gleShoppingData->parseGdataExceptionMessage(
                        $e->getMessage(),
                        $item->getProduct()
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    $errors[] = __('The item "%1" hasn\'t been deleted.', $item->getProduct()->getName());
                }
            }
        } else {
            return $this;
        }

        if ($totalDeleted > 0) {
            $this->_notifier->addNotice(
                __('Google Shopping item removal process succeded'),
                __('Total of %1 items(s) have been removed from Google Shopping.', $totalDeleted)
            );
        }
        if (count($errors)) {
            $this->_notifier->addMajor(__('Errors happened while deleting items from Google Shopping'), $errors);
        }

        return $this;
    }

    /**
     * Return items collection by IDs
     *
     * @param int[]|ItemCollection $items
     * @throws CoreException
     * @return null|ItemCollection
     */
    protected function _getItemsCollection($items)
    {
        $itemsCollection = null;
        if ($items instanceof ItemCollection) {
            $itemsCollection = $items;
        } elseif (is_array($items)) {
            $itemsCollection = $this->_collectionFactory->create()->addFieldToFilter('item_id', $items);
        }

        return $itemsCollection;
    }

    /**
     * Provides general error information
     *
     * @return void
     */
    protected function _addGeneralError()
    {
        if (!$this->_hasError) {
            $this->_notifier->addMajor(__('Google Shopping Error'), $this->_gleShoppingCategory->getMessage());
            $this->_hasError = true;
        }
    }
}
