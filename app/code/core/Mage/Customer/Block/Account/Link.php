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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account link
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Customer_Block_Account_Link extends Mage_Core_Block_Abstract
{
    /**
     * Add link to customer account page to the target block
     *
     * @param string $target
     * @param int $position
     * @return Mage_Customer_Block_Account_Link
     */
    public function addAccountLink($target, $position)
    {
        $helper = Mage::helper('Mage_Customer_Helper_Data');
        $this->_addLink(
            $target, $this->__('My Account'), $helper->getAccountUrl(), $this->__('My Account'), $position, '', ''
        );
        return $this;
    }

    /**
     * Add link to customer registration page to the target block
     *
     * @param string $target
     * @param int $position
     * @param string $textBefore
     * @param string $textAfter
     * @return Mage_Customer_Block_Account_Link
     */
    public function addRegisterLink($target, $position, $textBefore = '', $textAfter = '')
    {

        if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            $helper = Mage::helper('Mage_Customer_Helper_Data');
            $this->_addLink(
                $target,
                $this->__('register'),
                $helper->getRegisterUrl(),
                $this->__('register'),
                $position,
                $textBefore,
                $textAfter
            );
        }
        return $this;
    }

    /**
     * Add Log In/Out link to the target block
     *
     * @param string $target
     * @param int $position
     * @return Mage_Customer_Block_Account_Link
     */
    public function addAuthLink($target, $position)
    {
        $helper = Mage::helper('Mage_Customer_Helper_Data');
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            $this->_addLink(
                $target, $this->__('Log Out'), $helper->getLogoutUrl(), $this->__('Log Out'), $position, '', ''
            );
        } else {
            $this->_addLink(
                $target, $this->__('Log In'), $helper->getLoginUrl(), $this->__('Log In'), $position, '', ''
            );
        }
        return $this;
    }

    /**
     * Add link to the block with $target name
     *
     * @param string $target
     * @param string $text
     * @param string $url
     * @param string $title
     * @param int $position
     * @param string $textBefore
     * @param string $textAfter
     * @return Mage_Customer_Block_Account_Link
     */
    protected function _addLink($target, $text, $url, $title, $position, $textBefore='', $textAfter='')
    {
        $target = $this->getLayout()->getBlock($target);
        if ($target && method_exists($target, 'addLink')) {
            $target->addLink($text, $url, $title, false, array(), $position, null, null, $textBefore, $textAfter);
        }
        return $this;
    }
}
