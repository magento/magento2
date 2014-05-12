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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

/**
 * Adminhtml sales order create sidebar compared block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Compared extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_compared');
        $this->setDataId('compared');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Products in Comparison List');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            if ($collection = $this->getCreateOrderModel()->getCustomerCompareList()) {
                $collection = $collection->getItemCollection()->useProductItem(
                    true
                )->setStoreId(
                    $this->getQuote()->getStoreId()
                )->addStoreFilter(
                    $this->getQuote()->getStoreId()
                )->setCustomerId(
                    $this->getCustomerId()
                )->addAttributeToSelect(
                    'name'
                )->addAttributeToSelect(
                    'price'
                )->addAttributeToSelect(
                    'image'
                )->addAttributeToSelect(
                    'status'
                )->load();
            }
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get item id
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getItemId($item)
    {
        return $item->getCatalogCompareItemId();
    }
}
