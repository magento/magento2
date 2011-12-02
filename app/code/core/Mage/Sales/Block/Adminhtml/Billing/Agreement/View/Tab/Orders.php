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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml billing agreement related orders tab
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Block_Adminhtml_Billing_Agreement_View_Tab_Orders extends Mage_Adminhtml_Block_Sales_Order_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('billing_agreement_orders');
    }

    /**
     * Prepare related orders collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Grid_Collection');
        $collection->addBillingAgreementsFilter(Mage::registry('current_billing_agreement')->getId());
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Related Orders');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Related Orders');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ordersGrid', array('_current'=>true));
    }

    /**
     * Remove import/export field from grid
     *
     * @return bool
     */
    public function getExportTypes()
    {
        return false;
    }

    /**
     * Disable massaction in grid
     *
     * @return Mage_Sales_Block_Adminhtml_Billing_Agreement_View_Tab_Orders
     */
    protected function _prepareMassaction()
    {
        return $this;
    }
}
