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
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency
     */
    protected $_blockCurrency;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_curLocatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_currencyMock;

    /**
     * @var \Magento\Framework\Object
     */
    protected $_row;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->_localeMock = $this->getMock('Magento\Framework\Locale\CurrencyInterface');
        $this->_requestMock = $this->getMock('Magento\Framework\App\RequestInterface');

        $this->_curLocatorMock = $this->getMock(
            'Magento\Directory\Model\Currency\DefaultLocator',
            array(),
            array(),
            '',
            false
        );
        $this->_columnMock = $this->getMock(
            'Magento\Backend\Block\Widget\Grid\Column',
            array('getIndex'),
            array(),
            '',
            false
        );
        $this->_columnMock->expects($this->any())->method('getIndex')->will($this->returnValue('columnIndex'));

        $this->_currencyMock = $this->getMock('Magento\Directory\Model\Currency', array(), array(), '', false);
        $this->_currencyMock->expects($this->any())->method('load')->will($this->returnSelf());
        $currencyFactoryMock = $this->getMock(
            'Magento\Directory\Model\CurrencyFactory',
            array('create'),
            array(),
            '',
            false
        );
        $currencyFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_currencyMock));

        $this->_row = new \Magento\Framework\Object(array('columnIndex' => '10'));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_blockCurrency = $helper->getObject(
            'Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency',
            array(
                'storeManager' => $this->_storeManagerMock,
                'localeCurrency' => $this->_localeMock,
                'currencyLocator' => $this->_curLocatorMock,
                'request' => $this->_requestMock,
                'currencyFactory' => $currencyFactoryMock
            )
        );


        $this->_blockCurrency->setColumn($this->_columnMock);
    }

    protected function tearDown()
    {
        unset($this->_localeMock);
        unset($this->_curLocatorMock);
        unset($this->_columnMock);
        unset($this->_row);
        unset($this->_storeManagerMock);
        unset($this->_requestMock);
        unset($this->_blockCurrency);
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency::render
     */
    public function testRenderWithDefaultCurrency()
    {
        $this->_currencyMock->expects(
            $this->once()
        )->method(
            'getRate'
        )->with(
            'defaultCurrency'
        )->will(
            $this->returnValue(1.5)
        );

        $this->_curLocatorMock->expects(
            $this->any()
        )->method(
            'getDefaultCurrency'
        )->with(
            $this->_requestMock
        )->will(
            $this->returnValue('defaultCurrency')
        );

        $currLocaleMock = $this->getMock('Zend_Currency', array(), array(), '', false);
        $currLocaleMock->expects(
            $this->once()
        )->method(
            'toCurrency'
        )->with(
            15.0000
        )->will(
            $this->returnValue('15USD')
        );
        $this->_localeMock->expects(
            $this->once()
        )->method(
            'getCurrency'
        )->with(
            'defaultCurrency'
        )->will(
            $this->returnValue($currLocaleMock)
        );

        $this->assertEquals('15USD', $this->_blockCurrency->render($this->_row));
    }
}
