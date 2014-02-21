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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Comapare Products Sidebar Block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\Compare;

class Sidebar extends \Magento\Catalog\Block\Product\Compare\AbstractCompare
{
    /**
     * Compare Products Collection
     *
     * @var null|\Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    protected $_itemsCollection = null;

    /**
     * Initialize block
     */
    protected function _construct()
    {
        $this->setId('compare');
    }

    /**
     * Retrieve Compare Products Collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    public function getItems()
    {
        if ($this->_itemsCollection) {
            return $this->_itemsCollection;
        }
        return $this->_getHelper()->getItemCollection();
    }

    /**
     * Set Compare Products Collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection $collection
     * @return \Magento\Catalog\Block\Product\Compare\Sidebar
     */
    public function setItems($collection)
    {
        $this->_itemsCollection = $collection;
        return $this;
    }

    /**
     * Retrieve compare product helper
     *
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    public function getCompareProductHelper()
    {
        return $this->_getHelper();
    }

    /**
     * Retrieve Clean Compared Items URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->_getHelper()->getClearListUrl();
    }

    /**
     * Retrieve Full Compare page URL
     *
     * @return string
     */
    public function getCompareUrl()
    {
        return $this->_getHelper()->getListUrl();
    }
}
