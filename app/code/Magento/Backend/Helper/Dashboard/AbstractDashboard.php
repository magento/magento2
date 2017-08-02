<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper\Dashboard;

/**
 * Adminhtml abstract  dashboard helper.
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractDashboard extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Helper collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|array
     * @since 2.0.0
     */
    protected $_collection;

    /**
     * Parameters for helper
     *
     * @var array
     * @since 2.0.0
     */
    protected $_params = [];

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @since 2.0.0
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $this->_initCollection();
        }
        return $this->_collection;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    abstract protected function _initCollection();

    /**
     * Returns collection items
     *
     * @return array
     * @since 2.0.0
     */
    public function getItems()
    {
        return is_array($this->getCollection()) ? $this->getCollection() : $this->getCollection()->getItems();
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getCount()
    {
        return sizeof($this->getItems());
    }

    /**
     * @param string $index
     * @return array
     * @since 2.0.0
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
     * @param string $name
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * @param array $params
     * @return void
     * @since 2.0.0
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * @param string $name
     * @return mixed
     * @since 2.0.0
     */
    public function getParam($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }

        return null;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getParams()
    {
        return $this->_params;
    }
}
