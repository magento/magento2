<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

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
     * @var \Magento\Framework\DataObject
     */
    protected $_row;

    /*
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $numberFactoryMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_localeMock = $this->getMock(\Magento\Framework\Locale\CurrencyInterface::class);
        $this->_requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);

        $this->_curLocatorMock = $this->getMock(
            \Magento\Directory\Model\Currency\DefaultLocator::class,
            [],
            [],
            '',
            false
        );
        $this->_columnMock = $this->getMock(
            \Magento\Backend\Block\Widget\Grid\Column::class,
            ['getIndex'],
            [],
            '',
            false
        );
        $this->_columnMock->expects($this->any())->method('getIndex')->will($this->returnValue('columnIndex'));

        $this->_currencyMock = $this->getMock(\Magento\Directory\Model\Currency::class, [], [], '', false);
        $this->_currencyMock->expects($this->any())->method('load')->will($this->returnSelf());
        $currencyFactoryMock = $this->getMock(
            \Magento\Directory\Model\CurrencyFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $currencyFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_currencyMock));

        $this->_row = new \Magento\Framework\DataObject(['columnIndex' => '$10.00']);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_blockCurrency = $helper->getObject(
            \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency::class,
            [
                'storeManager' => $this->_storeManagerMock,
                'localeCurrency' => $this->_localeMock,
                'currencyLocator' => $this->_curLocatorMock,
                'request' => $this->_requestMock,
                'currencyFactory' => $currencyFactoryMock
            ]
        );

        $this->_blockCurrency->setColumn($this->_columnMock);

        $this->numberFormatterFactoryMock = $this->getMock(
            \Magento\Framework\Intl::class,
            ['create'],
            [],
            '',
            false
        );
        $helper->setBackwardCompatibleProperty(
            $this->_blockCurrency,
            'numberFormatterFactory',
            $this->numberFormatterFactoryMock
        );
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
        $this->_currencyMock->expects($this->once())
            ->method('getRate')
            ->with('USD')
            ->willReturn(1.5);
        $currLocaleMock = $this->getMock(\Magento\Framework\Currency::class, ['getLocale','toCurrency'], [], '', false);
        $currLocaleMock->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');
        $this->_localeMock->expects($this->once())
            ->method('getCurrency')
            ->with('USD')
            ->will($this->returnValue($currLocaleMock));

        $numberFormatterMock = $this->getMock(\NumberFormatter::class, ['parseCurrency'], [], '', false);
        $this->numberFormatterFactoryMock->expects($this->once())
            ->method('create')
            ->with('en_US', \NumberFormatter::CURRENCY)
            ->will($this->returnValue($numberFormatterMock));

        $numberFormatterMock->expects($this->once())
            ->method('parseCurrency')
            ->with('$10.00', 'USD')
            ->willReturn(10);

        $this->_curLocatorMock->expects($this->any())
            ->method('getDefaultCurrency')
            ->with($this->_requestMock)
            ->willReturn('USD');

        $currLocaleMock->expects($this->once())
            ->method('toCurrency')
            ->with(15.0000)
            ->willReturn('$15.00');

        $this->assertEquals('$15.00', $this->_blockCurrency->render($this->_row));
    }
}
