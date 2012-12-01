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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout payment information data
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Multishipping_Payment_Info extends Mage_Payment_Block_Info_ContainerAbstract
{
    /**
     * Retrieve payment info model
     *
     * @return Mage_Payment_Model_Info
     */
    public function getPaymentInfo()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Type_Multishipping')->getQuote()->getPayment();
    }

    protected function _toHtml()
    {
        $html = '';
        if ($block = $this->getChildBlock($this->_getInfoBlockName())) {
            $html = $block->toHtml();
        }
        return $html;
    }
}
