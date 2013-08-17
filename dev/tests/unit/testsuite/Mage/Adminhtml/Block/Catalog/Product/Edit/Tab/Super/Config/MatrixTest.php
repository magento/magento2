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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_MatrixTest extends PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Matrix
     */
    protected $_block;

    /** @var Mage_Backend_Block_Template_Context|PHPUnit_Framework_MockObject_MockObject */
    protected $_context;

    /** @var Mage_Core_Model_App|PHPUnit_Framework_MockObject_MockObject */
    protected $_application;

    /** @var Mage_Core_Model_LocaleInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $_locale;

    protected function setUp()
    {
        $this->_context = $this->getMock('Mage_Backend_Block_Template_Context', array(), array(), '', false);
        $this->_application = $this->getMock('Mage_Core_Model_App', array(), array(), '', false);
        $this->_locale = $this->getMock('Mage_Core_Model_LocaleInterface', array(), array(), '', false);
        $this->_block = new Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Matrix(
            $this->_context,
            $this->_application,
            $this->_locale
        );
    }

    public function testRenderPrice()
    {
        $this->_application->expects($this->once())
            ->method('getBaseCurrencyCode')->with()->will($this->returnValue('USD'));
        $currency = $this->getMock('Zend_Currency', array(), array(), '', false);
        $currency->expects($this->once())
            ->method('toCurrency')->with('100.0000')->will($this->returnValue('$100.00'));
        $this->_locale->expects($this->once())
            ->method('currency')->with('USD')->will($this->returnValue($currency));
        $this->assertEquals('$100.00', $this->_block->renderPrice(100));
    }
}
