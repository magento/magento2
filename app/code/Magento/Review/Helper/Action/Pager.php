<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Review\Helper\Action;

use Magento\Framework\Exception\LocalizedException;

/**
 * Action pager helper for iterating over search results
 *
 * @api
 * @since 2.0.0
 */
class Pager extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STORAGE_PREFIX = 'search_result_ids';

    /**
     * Storage id
     *
     * @var int
     * @since 2.0.0
     */
    protected $_storageId = null;

    /**
     * Array of items
     *
     * @var array
     * @since 2.0.0
     */
    protected $_items = null;

    /**
     * Backend session model
     *
     * @var \Magento\Backend\Model\Session
     * @since 2.0.0
     */
    protected $_backendSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Session $backendSession
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Backend\Model\Session $backendSession)
    {
        $this->_backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Set storage id
     *
     * @param int $storageId
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _loadItems()
    {
        if (is_null($this->_items)) {
            $this->_items = (array)$this->_backendSession->getData($this->_getStorageKey());
        }
    }

    /**
     * Get next item id
     *
     * @param int $id
     * @return int|bool
     * @since 2.0.0
     */
    public function getNextItemId($id)
    {
        $position = $this->_findItemPositionByValue($id);
        if ($position === false || $position == count($this->_items) - 1) {
            return false;
        }

        return $this->_items[$position + 1];
    }

    /**
     * Get previous item id
     *
     * @param int $id
     * @return int|bool
     * @since 2.0.0
     */
    public function getPreviousItemId($id)
    {
        $position = $this->_findItemPositionByValue($id);
        if ($position === false || $position == 0) {
            return false;
        }

        return $this->_items[$position - 1];
    }

    /**
     * Return item position based on passed in value
     *
     * @param mixed $value
     * @return int|bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getStorageKey()
    {
        if (!$this->_storageId) {
            throw new LocalizedException(__('Storage key was not set'));
        }

        return self::STORAGE_PREFIX . $this->_storageId;
    }
}
