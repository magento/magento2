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
 * Admin tax class content block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Tax_Class extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller      = 'tax_class';
        parent::__construct();
    }

    public function setClassType($classType)
    {
        if ($classType == Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT) {
            $this->_headerText      = Mage::helper('Mage_Tax_Helper_Data')->__('Product Tax Classes');
            $this->_addButtonLabel  = Mage::helper('Mage_Tax_Helper_Data')->__('Add New Class');
        }
        elseif ($classType == Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER) {
            $this->_headerText      = Mage::helper('Mage_Tax_Helper_Data')->__('Customer Tax Classes');
            $this->_addButtonLabel  = Mage::helper('Mage_Tax_Helper_Data')->__('Add New Class');
        }

        $this->getChild('grid')->setClassType($classType);
        $this->setData('class_type', $classType);

        return $this;
    }
}
