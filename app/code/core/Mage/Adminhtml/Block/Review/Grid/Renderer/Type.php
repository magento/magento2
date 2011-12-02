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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml review grid item renderer for item type
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Review_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {

        if (is_null($row->getCustomerId())) {
            if ($row->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) {
                return Mage::helper('Mage_Review_Helper_Data')->__('Administrator');
            } else {
                return Mage::helper('Mage_Review_Helper_Data')->__('Guest');
            }
        } elseif ($row->getCustomerId() > 0) {
            return Mage::helper('Mage_Review_Helper_Data')->__('Customer');
        }
//		return ($row->getCustomerId() ? Mage::helper('Mage_Review_Helper_Data')->__('Customer') : Mage::helper('Mage_Review_Helper_Data')->__('Guest'));
    }
}// Class Mage_Adminhtml_Block_Review_Grid_Renderer_Type END
