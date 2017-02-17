<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\Component\Listing\AssociatedProduct\Columns;

use Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns\Price as PriceColumn;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Locale\CurrencyInterface as LocaleCurrency;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\Processor as UiElementProcessor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Currency;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceColumn
     */
    private $priceColumn;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var LocaleCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeCurrencyMock;

    /**
     * @var UiElementProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiElementProcessorMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyMock;

    /**
     * @var CurrencyModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyModelMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->localeCurrencyMock = $this->getMockBuilder(LocaleCurrency::class)
            ->getMockForAbstractClass();
        $this->uiElementProcessorMock = $this->getMockBuilder(UiElementProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseCurrency', 'getBaseCurrencyCode'])
            ->getMockForAbstractClass();
        $this->currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyModelMock = $this->getMockBuilder(CurrencyModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects(static::any())
            ->method('getProcessor')
            ->willReturn($this->uiElementProcessorMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->priceColumn = $this->objectManagerHelper->getObject(
            PriceColumn::class,
            [
                'context' => $this->contextMock,
                'localeCurrency' => $this->localeCurrencyMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testPrepareDataSource()
    {
        $fieldName = 'special_field';
        $baseCurrencyCode = 'USD';
        $currencySymbol = '$';
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'id' => '1',
                        $fieldName => 3
                    ],
                    [
                        'id' => '2'
                    ],
                    [
                        'id' => '3',
                        $fieldName => 4.55,
                    ]
                ]
            ]
        ];
        $result = [
            'data' => [
                'items' => [
                    [
                        'id' => '1',
                        $fieldName => '3.00$',
                        'price_number' => '3.00',
                        'price_currency' => $currencySymbol
                    ],
                    [
                        'id' => '2'
                    ],
                    [
                        'id' => '3',
                        $fieldName => '4.55$',
                        'price_number' => '4.55',
                        'price_currency' => $currencySymbol
                    ]
                ]
            ]
        ];

        $this->contextMock->expects($this->any())
            ->method('getFilterParam')
            ->with('store_id', Store::DEFAULT_STORE_ID)
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(Store::DEFAULT_STORE_ID)
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getBaseCurrencyCode')
            ->willReturn($baseCurrencyCode);
        $this->localeCurrencyMock->expects($this->any())
            ->method('getCurrency')
            ->with($baseCurrencyCode)
            ->willReturn($this->currencyMock);
        $this->currencyMock->expects($this->any())
            ->method('toCurrency')
            ->willReturnMap(
                [
                    ['3.000000', ['display' => false], '3.00'],
                    ['4.550000', ['display' => false], '4.55'],
                    ['3.000000', [], '3.00$'],
                    ['4.550000', [], '4.55$']
                ]
            );
        $this->storeMock->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn($this->currencyModelMock);
        $this->currencyModelMock->expects($this->any())
            ->method('getCurrencySymbol')
            ->willReturn($currencySymbol);

        $this->priceColumn->setData('name', $fieldName);

        $this->assertSame($result, $this->priceColumn->prepareDataSource($dataSource));
    }
}
