<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->_storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->_localeMock = $this->createMock(CurrencyInterface::class);
        $this->_requestMock = $this->createMock(RequestInterface::class);

        $this->_curLocatorMock = $this->createMock(DefaultLocator::class);
        $this->_columnMock = $this->createPartialMock(Column::class, ['getIndex']);
        $this->_columnMock->expects($this->any())->method('getIndex')->will($this->returnValue('columnIndex'));

        $this->_currencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->_currencyMock->expects($this->any())->method('load')->will($this->returnSelf());
        $currencyFactoryMock = $this->createPartialMock(CurrencyFactory::class, ['create']);
        $currencyFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_currencyMock));

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

        $currLocaleMock = $this->createMock(\Zend_Currency::class);
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
