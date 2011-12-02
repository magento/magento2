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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract message model
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Core_Model_Message_Abstract
{
    protected $_type;
    protected $_code;
    protected $_class;
    protected $_method;
    protected $_identifier;
    protected $_isSticky = false;

    public function __construct($type, $code='')
    {
        $this->_type = $type;
        $this->_code = $code;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getText()
    {
        return $this->getCode();
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setClass($class)
    {
        $this->_class = $class;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
    }

    public function toString()
    {
        $out = $this->getType().': '.$this->getText();
        return $out;
    }

    /**
     * Set message identifier
     *
     * @param string $id
     * @return Mage_Core_Model_Message_Abstract
     */
    public function setIdentifier($id)
    {
        $this->_identifier = $id;
        return $this;
    }

    /**
     * Get message identifier
     *
     *  @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Set message sticky status
     *
     * @param bool $isSticky
     * @return Mage_Core_Model_Message_Abstract
     */
    public function setIsSticky($isSticky = true)
    {
        $this->_isSticky = $isSticky;
        return $this;
    }

    /**
     * Get whether message is sticky
     *
     * @return bool
     */
    public function getIsSticky()
    {
        return $this->_isSticky;
    }
}
