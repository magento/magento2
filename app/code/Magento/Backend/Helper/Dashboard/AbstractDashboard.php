<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper\Dashboard;

/**
 * Adminhtml abstract  dashboard helper.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractDashboard extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Helper collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|array
     */
    protected $_collection;

    /**
     * Parameters for helper
     *
     * @var array
     */
    protected $_params = [];

    /**
     * Return collections
     *
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $this->_initCollection();
        }
        return $this->_collection;
    }

    /**
     * Init collections
     *
     * @return void
     */
    abstract protected function _initCollection();

    /**
     * Returns collection items
     *
     * @return array
     */
    public function getItems()
    {
        return is_array($this->getCollection()) ? $this->getCollection() : $this->getCollection()->getItems();
    }

    /**
     * Return items count
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->getItems());
    }

    /**
     * Return column
     *
     * @param string $index
     * @return array
     */
    public function getColumn($index)
    {
        $result = [];
        foreach ($this->getItems() as $item) {
            if (is_array($item)) {
                if (isset($item[$index])) {
                    $result[] = $item[$index];
                } else {
                    $result[] = null;
                }
            } elseif ($item instanceof \Magento\Framework\DataObject) {
                $result[] = $item->getData($index);
            } else {
                $result[] = null;
            }
        }
        return $result;
    }

    /**
     * Set params with value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * Set params
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * Get params with name
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }

        return null;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
}
