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
 * Translate expression object
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Translate_Expr
{
    protected $_text;
    protected $_module;
    
    public function __construct($text='', $module='') 
    {
        $this->_text    = $text;
        $this->_module  = $module;
    }
    
    public function setText($text)
    {
        $this->_text = $text;
        return $this;
    }
    
    public function setModule($module)
    {
        $this->_module = $module;
        return $this;
    }
    
    /**
     * Retrieve expression text
     *
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }
    
    /**
     * Retrieve expression module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }
    
    /**
     * Retrieve expression code
     *
     * @param   string $separator
     * @return  string
     */
    public function getCode($separator='::')
    {
        return $this->getModule().$separator.$this->getText();
    }
}
