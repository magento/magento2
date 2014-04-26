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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config;

class MatrixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix
     */
    protected $_block;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appConfig;

    /** @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_locale;

    protected function setUp()
    {
        $this->_appConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            'Magento\Backend\Block\Template\Context',
            array('scopeConfig' => $this->_appConfig)
        );
        $this->_locale = $this->getMock('Magento\Framework\Locale\CurrencyInterface', array(), array(), '', false);
        $data = array(
            'context' => $context,
            'localeCurrency' => $this->_locale,
            'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', array(), array(), '', false),
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false)
        );
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $helper->getObject('Magento\Backend\Block\System\Config\Form', $data);
        $this->_block = $helper->getObject(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix',
            $data
        );
    }

    public function testRenderPrice()
    {
        $this->_appConfig->expects($this->once())->method('getValue')->will($this->returnValue('USD'));
        $currency = $this->getMock('Zend_Currency', array(), array(), '', false);
        $currency->expects($this->once())->method('toCurrency')->with('100.0000')->will($this->returnValue('$100.00'));
        $this->_locale->expects(
            $this->once()
        )->method(
            'getCurrency'
        )->with(
            'USD'
        )->will(
            $this->returnValue($currency)
        );
        $this->assertEquals('$100.00', $this->_block->renderPrice(100));
    }
}
