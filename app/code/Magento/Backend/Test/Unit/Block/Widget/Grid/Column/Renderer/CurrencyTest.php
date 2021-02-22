<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency
     */
    protected $_blockCurrency;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_localeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_curLocatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_columnMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_currencyMock;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_row;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_localeMock = $this->createMock(\Magento\Framework\Locale\CurrencyInterface::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->_curLocatorMock = $this->createMock(\Magento\Directory\Model\Currency\DefaultLocator::class);
        $this->_columnMock = $this->createPartialMock(\Magento\Backend\Block\Widget\Grid\Column::class, ['getIndex']);
        $this->_columnMock->expects($this->any())->method('getIndex')->willReturn('columnIndex');

        $this->_currencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->_currencyMock->expects($this->any())->method('load')->willReturnSelf();
        $currencyFactoryMock = $this->createPartialMock(\Magento\Directory\Model\CurrencyFactory::class, ['create']);
        $currencyFactoryMock->expects($this->any())->method('create')->willReturn($this->_currencyMock);

        $this->_row = new \Magento\Framework\DataObject(['columnIndex' => '10']);

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
        )->willReturn(
            1.5
        );

        $this->_curLocatorMock->expects(
            $this->any()
        )->method(
            'getDefaultCurrency'
        )->with(
            $this->_requestMock
        )->willReturn(
            'defaultCurrency'
        );

        $currLocaleMock = $this->createMock(\Zend_Currency::class);
        $currLocaleMock->expects(
            $this->once()
        )->method(
            'toCurrency'
        )->with(
            15.0000
        )->willReturn(
            '15USD'
        );
        $this->_localeMock->expects(
            $this->once()
        )->method(
            'getCurrency'
        )->with(
            'defaultCurrency'
        )->willReturn(
            $currLocaleMock
        );

        $this->assertEquals('15USD', $this->_blockCurrency->render($this->_row));
    }
}
