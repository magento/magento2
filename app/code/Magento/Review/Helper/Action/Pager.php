<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Review\Helper\Action;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;

/**
 * Action pager helper for iterating over search results
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Pager extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected const STORAGE_PREFIX = 'search_result_ids';

    /**
     * Key identifier for session storage id
     *
     * @var int
     */
    protected $_storageId = null;

    /**
     * Array of items
     *
     * @var array
     */
    protected $_items = null;

    /**
     * Backend session model
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * Review collection model factory
     *
     * @var CollectionFactory|null
     */
    protected $reviewCollectionFactory = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Session $backendSession
     * @param CollectionFactory|null $reviewCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Session $backendSession,
        ?CollectionFactory $reviewCollectionFactory = null
    ) {
        $this->_backendSession = $backendSession;
       $this->reviewCollectionFactory = $reviewCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        parent::__construct($context);
    }

    /**
     * Set storage id
     *
     * @param int $storageId
     * @return void
     * @deprecated This method is no longer used for setting storage id.We use it only to support backward compatibility
     * @see self::getRelativeReviewId()
     */
    public function setStorageId($storageId)
    {
        $this->_storageId = $storageId;
    }

    /**
     * Set items to storage
     *
     * @param array $items
     * @return $this
     * @deprecated This method is not being used.We use it only to support compatibility
     * @see self::getRelativeReviewId()
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        $this->_backendSession->setData($this->_getStorageKey(), $this->_items);

        return $this;
    }

    /**
     * Load stored items
     *
     * @return void
     * @deprecated This method is not being used.We use it only to support compatibility
     * @see self::getRelativeReviewId()
     */
    protected function _loadItems()
    {
        if ($this->_items === null) {
            $this->_items = (array)$this->_backendSession->getData($this->_getStorageKey());
        }
    }

    /**
     * Get the next review id.
     *
     * @param int $id
     * @return int|bool
     */
    public function getNextItemId($id): int|bool
    {
        return $this->getRelativeReviewId($id, 'gt', 'ASC');
    }

    /**
     * Get the previous review id.
     *
     * @param int $id
     * @return int|bool
     */
    public function getPreviousItemId($id): int|bool
    {
        return $this->getRelativeReviewId($id, 'lt', 'DESC');
    }

    /**
     * Return item position based on passed in value
     *
     * @param mixed $value
     * @return int|bool
     * @deprecated This method is not being used.We use it only to support compatibility
     * @see self::getRelativeReviewId()
     */
    protected function _findItemPositionByValue($value)
    {
        $this->_loadItems();
        return array_search($value, $this->_items);
    }

    /**
     * Get storage key
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated This method is not being used.We use it only to support compatibility
     * @see self::getRelativeReviewId()
     */
    protected function _getStorageKey()
    {
        if (!$this->_storageId) {
            throw new LocalizedException(__("The storage key wasn't set. Add the storage key and try again."));
        }

        return self::STORAGE_PREFIX . $this->_storageId;
    }

    /**
     * Get the review id based on comparison and order.
     *
     * @param int $id
     * @param string $operator
     * @param string $order
     * @return int|bool
     */
    private function getRelativeReviewId($id, $operator, $order): int|bool
    {
        $collection = $this->reviewCollectionFactory->create();
        $collection->addFieldToFilter('main_table.review_id', [$operator => $id])
            ->setOrder('main_table.review_id', $order)
            ->setPageSize(1)
            ->setCurPage(1);

        $item = $collection->getFirstItem();
        return $item->getId() ? (int)$item->getId() : false;
    }
}
