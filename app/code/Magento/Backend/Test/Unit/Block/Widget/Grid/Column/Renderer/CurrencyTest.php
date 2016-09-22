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
    protected $priceCurrencyMock;

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

        $this->priceCurrencyMock = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['convertAndFormat']
        );
        $helper->setBackwardCompatibleProperty(
            $this->_blockCurrency,
            'priceCurrency',
            $this->priceCurrencyMock
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
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndFormat')
            ->with('$10.00', false)
            ->willReturn('$10.00');

        $this->assertEquals('$10.00', $this->_blockCurrency->render($this->_row));
    }
}
