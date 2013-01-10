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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 *
 * @method Mage_Checkout_Model_Resource_Agreement _getResource()
 * @method Mage_Checkout_Model_Resource_Agreement getResource()
 * @method string getName()
 * @method Mage_Checkout_Model_Agreement setName(string $value)
 * @method string getContent()
 * @method Mage_Checkout_Model_Agreement setContent(string $value)
 * @method string getContentHeight()
 * @method Mage_Checkout_Model_Agreement setContentHeight(string $value)
 * @method string getCheckboxText()
 * @method Mage_Checkout_Model_Agreement setCheckboxText(string $value)
 * @method int getIsActive()
 * @method Mage_Checkout_Model_Agreement setIsActive(int $value)
 * @method int getIsHtml()
 * @method Mage_Checkout_Model_Agreement setIsHtml(int $value)
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Agreement extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('Mage_Checkout_Model_Resource_Agreement');
    }
}
