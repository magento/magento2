<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->_localeMock = $this->getMock('Magento\Framework\Locale\CurrencyInterface');
        $this->_requestMock = $this->getMock('Magento\Framework\App\RequestInterface');

        $this->_curLocatorMock = $this->getMock(
            'Magento\Directory\Model\Currency\DefaultLocator',
            [],
            [],
            '',
            false
        );
        $this->_columnMock = $this->getMock(
            'Magento\Backend\Block\Widget\Grid\Column',
            ['getIndex'],
            [],
            '',
            false
        );
        $this->_columnMock->expects($this->any())->method('getIndex')->will($this->returnValue('columnIndex'));

        $this->_currencyMock = $this->getMock('Magento\Directory\Model\Currency', [], [], '', false);
        $this->_currencyMock->expects($this->any())->method('load')->will($this->returnSelf());
        $currencyFactoryMock = $this->getMock(
            'Magento\Directory\Model\CurrencyFactory',
            ['create'],
            [],
            '',
            false
        );
        $currencyFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_currencyMock));

        $this->_row = new \Magento\Framework\Object(['columnIndex' => '10']);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_blockCurrency = $helper->getObject(
            'Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency',
            [
                'storeManager' => $this->_storeManagerMock,
                'localeCurrency' => $this->_localeMock,
                'currencyLocator' => $this->_curLocatorMock,
                'request' => $this->_requestMock,
                'currencyFactory' => $currencyFactoryMock
            ]
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

        $currLocaleMock = $this->getMock('Zend_Currency', [], [], '', false);
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
