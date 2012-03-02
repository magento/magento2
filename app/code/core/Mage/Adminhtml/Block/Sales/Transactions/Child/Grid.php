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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml transaction details grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Transactions_Child_Grid extends Mage_Adminhtml_Block_Sales_Transactions_Grid
{
    /**
     * Columns, that should be removed from grid
     *
     * @var array
     */
    protected $_columnsToRemove = array('parent_id', 'parent_txn_id');

    /**
     * Disable pager and filter
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionChildGrid');
        $this->setDefaultSort('created_at');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Add filter by parent transaction ID
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection');
        $collection->addParentIdFilter(Mage::registry('current_transaction')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Remove some columns and make other not sortable
     *
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        foreach ($this->_columns as $key => $value) {
            if (in_array($key, $this->_columnsToRemove)) {
                unset($this->_columns[$key]);
            } else {
                $this->_columns[$key]->setData('sortable', false);
            }
        }
        return $result;
    }
}
