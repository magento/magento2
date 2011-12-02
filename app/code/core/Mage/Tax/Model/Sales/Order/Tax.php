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

/**
 * Enter description here ...
 *
 * @method Mage_Tax_Model_Resource_Sales_Order_Tax _getResource()
 * @method Mage_Tax_Model_Resource_Sales_Order_Tax getResource()
 * @method int getOrderId()
 * @method Mage_Tax_Model_Sales_Order_Tax setOrderId(int $value)
 * @method string getCode()
 * @method Mage_Tax_Model_Sales_Order_Tax setCode(string $value)
 * @method string getTitle()
 * @method Mage_Tax_Model_Sales_Order_Tax setTitle(string $value)
 * @method float getPercent()
 * @method Mage_Tax_Model_Sales_Order_Tax setPercent(float $value)
 * @method float getAmount()
 * @method Mage_Tax_Model_Sales_Order_Tax setAmount(float $value)
 * @method int getPriority()
 * @method Mage_Tax_Model_Sales_Order_Tax setPriority(int $value)
 * @method int getPosition()
 * @method Mage_Tax_Model_Sales_Order_Tax setPosition(int $value)
 * @method float getBaseAmount()
 * @method Mage_Tax_Model_Sales_Order_Tax setBaseAmount(float $value)
 * @method int getProcess()
 * @method Mage_Tax_Model_Sales_Order_Tax setProcess(int $value)
 * @method float getBaseRealAmount()
 * @method Mage_Tax_Model_Sales_Order_Tax setBaseRealAmount(float $value)
 * @method int getHidden()
 * @method Mage_Tax_Model_Sales_Order_Tax setHidden(int $value)
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Sales_Order_Tax extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('Mage_Tax_Model_Resource_Sales_Order_Tax');
    }
}
