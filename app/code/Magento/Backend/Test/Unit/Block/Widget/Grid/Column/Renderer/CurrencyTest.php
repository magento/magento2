<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencyTest extends TestCase
{
    /**
     * @var Currency
     */
    protected $_blockCurrency;

    /**
     * @var MockObject
     */
    protected $_localeMock;

    /**
     * @var MockObject
     */
    protected $_curLocatorMock;

    /**
     * @var MockObject
     */
    protected $_columnMock;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_currencyMock;

    /**
     * @var DataObject
     */
    protected $_row;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->_localeMock = $this->getMockForAbstractClass(CurrencyInterface::class);
        $this->_requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->_curLocatorMock = $this->createMock(DefaultLocator::class);
        $this->_columnMock = $this->getMockBuilder(Column::class)
            ->addMethods(['getIndex', 'getCurrency', 'getRateField'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_columnMock->expects($this->any())->method('getIndex')->willReturn('columnIndex');

        $this->_currencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->_currencyMock->expects($this->any())->method('load')->willReturnSelf();
        $currencyFactoryMock = $this->createPartialMock(CurrencyFactory::class, ['create']);
        $currencyFactoryMock->expects($this->any())->method('create')->willReturn($this->_currencyMock);

        $this->_row = new DataObject(['columnIndex' => '10']);

        $helper = new ObjectManager($this);
        $this->_blockCurrency = $helper->getObject(
            Currency::class,
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

    protected function tearDown(): void
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
            ->with('defaultCurrency')
            ->willReturn(1.5);
        $this->_curLocatorMock->expects($this->any())
            ->method('getDefaultCurrency')
            ->with($this->_requestMock)
            ->willReturn('defaultCurrency');
        $currLocaleMock = $this->createMock(\Zend_Currency::class);
        $currLocaleMock->expects($this->once())
            ->method('toCurrency')
            ->with(15.0000)
            ->willReturn('15USD');
        $this->_localeMock->expects($this->once())
            ->method('getCurrency')
            ->with('defaultCurrency')
            ->willReturn($currLocaleMock);
        $this->_columnMock->method('getCurrency')->willReturn('USD');
        $this->_columnMock->method('getRateField')->willReturn('test_rate_field');

        $this->assertEquals('15USD', $this->_blockCurrency->render($this->_row));
    }
}
