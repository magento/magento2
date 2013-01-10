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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders grid massaction items updater
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Grid_Massaction_ItemsUpdater implements Mage_Core_Model_Layout_Argument_UpdaterInterface
{
    /**
     * Remove massaction items in case they disallowed for user
     * @param mixed $argument
     * @return mixed
     */
    public function update($argument)
    {
        if (false === Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Sales::cancel')) {
            unset($argument['cancel_order']);
        }

        if (false === Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Sales::hold')) {
            unset($argument['hold_order']);
        }

        if (false === Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Sales::unhold')) {
            unset($argument['unhold_order']);
        }

        return $argument;
    }
}
