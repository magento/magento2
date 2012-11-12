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
 * Adminhtml newsletter subscribers grid website filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter_Grid_Filter_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{

    protected static $_statuses;

    protected function _construct()
    {
        self::$_statuses = array(
                null                                        => null,
                Mage_Newsletter_Model_Queue::STATUS_SENT    => Mage::helper('Mage_Customer_Helper_Data')->__('Sent'),
                Mage_Newsletter_Model_Queue::STATUS_CANCEL  => Mage::helper('Mage_Customer_Helper_Data')->__('Cancel'),
                Mage_Newsletter_Model_Queue::STATUS_NEVER   => Mage::helper('Mage_Customer_Helper_Data')->__('Not Sent'),
                Mage_Newsletter_Model_Queue::STATUS_SENDING => Mage::helper('Mage_Customer_Helper_Data')->__('Sending'),
                Mage_Newsletter_Model_Queue::STATUS_PAUSE   => Mage::helper('Mage_Customer_Helper_Data')->__('Paused'),
            );
        parent::_construct();
    }

    protected function _getOptions()
    {
        $result = array();
        foreach (self::$_statuses as $code=>$label) {
            $result[] = array('value'=>$code, 'label'=>Mage::helper('Mage_Customer_Helper_Data')->__($label));
        }

        return $result;
    }

    public function getCondition()
    {
        if(is_null($this->getValue())) {
            return null;
        }

        return array('eq'=>$this->getValue());
    }

}
