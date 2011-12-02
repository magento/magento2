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
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Tax_Model_System_Config_Source_Algorithm
{
    protected $_options;

    public function __construct()
    {
        $this->_options = array(
            array(
                'value' => Mage_Tax_Model_Calculation::CALC_UNIT_BASE,
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Unit Price')
            ),
            array(
                'value' => Mage_Tax_Model_Calculation::CALC_ROW_BASE,
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Row Total')
            ),
            array(
                'value' => Mage_Tax_Model_Calculation::CALC_TOTAL_BASE,
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Total')
            ),
        );
    }

    public function toOptionArray()
    {
        return $this->_options;
    }
}
