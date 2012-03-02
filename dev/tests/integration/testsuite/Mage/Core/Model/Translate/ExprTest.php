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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Translate_ExprTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Translate_Expr
     */
    protected $_model;

    protected $_expectedText   = __FILE__;
    protected $_expectedModule = __CLASS__;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Translate_Expr($this->_expectedText, $this->_expectedModule);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->_expectedText, $this->_model->getText());
        $this->assertEquals($this->_expectedModule, $this->_model->getModule());
    }

    public function testSetTextSetModule()
    {
        $expectedText = __FILE__ . '!!!';
        $expectedModule = __CLASS__ . '!!!';
        $this->_model->setText($expectedText);
        $this->_model->setModule($expectedModule);
        $this->assertEquals($expectedText, $this->_model->getText());
        $this->assertEquals($expectedModule, $this->_model->getModule());
    }

    public function testGetCode()
    {
        $this->assertEquals($this->_expectedModule . '::' . $this->_expectedText, $this->_model->getCode());
        $this->assertEquals($this->_expectedModule . '##' . $this->_expectedText, $this->_model->getCode('##'));
    }
}
