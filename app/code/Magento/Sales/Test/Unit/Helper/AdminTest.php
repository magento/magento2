<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Config|MockObject
     */
    protected $salesConfigMock;

    /**
     * @var DataObject|MockObject
     */
    protected $magentoObjectMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var Admin
     */
    protected $adminHelper;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->salesConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();

        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminHelper = (new ObjectManager($this))->getObject(
            Admin::class,
            [
                'context' => $this->contextMock,
                'storeManager' => $this->storeManagerMock,
                'salesConfig' => $this->salesConfigMock,
                'priceCurrency' => $this->priceCurrency,
                'escaper' => $this->escaperMock
            ]
        );

        $this->magentoObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getData'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('formatBasePrice')
            ->willReturn('formattedBasePrice');
        $this->orderMock->expects($this->any())
            ->method('formatPrice')
            ->willReturn('formattedPrice');
        $this->orderMock->expects($this->any())
            ->method('getData')
            ->willReturn('data');
    }

    /**
     * @param string $expected
     * @param bool $dataObjectIsOrder
     * @param bool $isCurrencyDifferent
     * @param bool $magentoDataObjectHasOrder
     * @param bool $strong
     * @param string $separator
     * @dataProvider displayPricesDataProvider
     */
    public function testDisplayPrices(
        $expected,
        $dataObjectIsOrder,
        $isCurrencyDifferent = true,
        $magentoDataObjectHasOrder = true,
        $strong = false,
        $separator = '<br/>'
    ) {
        $this->orderMock->expects($this->any())
            ->method('isCurrencyDifferent')
            ->willReturn($isCurrencyDifferent);
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->priceCurrency->expects($this->any())
            ->method('format')
            ->willReturn('storeFormattedPrice');
        $dataObject = $this->orderMock;
        if (!$dataObjectIsOrder) {
            $returnRes = false;
            if ($magentoDataObjectHasOrder) {
                $returnRes = $this->orderMock;
            }
            $this->magentoObjectMock->expects($this->once())
                ->method('getOrder')
                ->willReturn($returnRes);
            $dataObject = $this->magentoObjectMock;
        }
        $basePrice = 10.00;
        $price = 15.00;
        $this->assertEquals(
            $expected,
            $this->adminHelper->displayPrices($dataObject, $basePrice, $price, $strong, $separator)
        );
    }

    /**
     * @param string $expected
     * @param bool $dataObjectIsOrder
     * @param bool $isCurrencyDifferent
     * @param bool $magentoDataObjectHasOrder
     * @param bool $strong
     * @param string $separator
     * @dataProvider displayPricesDataProvider
     */
    public function testDisplayPriceAttribute(
        $expected,
        $dataObjectIsOrder,
        $isCurrencyDifferent = true,
        $magentoDataObjectHasOrder = true,
        $strong = false,
        $separator = '<br/>'
    ) {
        $this->orderMock->expects($this->any())
            ->method('isCurrencyDifferent')
            ->willReturn($isCurrencyDifferent);
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->priceCurrency->expects($this->any())
            ->method('format')
            ->willReturn('storeFormattedPrice');
        $dataObject = $this->orderMock;
        if (!$dataObjectIsOrder) {
            $returnRes = false;
            if ($magentoDataObjectHasOrder) {
                $returnRes = $this->orderMock;
            }
            $this->magentoObjectMock->expects($this->once())
                ->method('getOrder')
                ->willReturn($returnRes);
            $this->magentoObjectMock->expects($this->any())
                ->method('getData')
                ->willReturn('data');
            $dataObject = $this->magentoObjectMock;
        }
        $this->assertEquals(
            $expected,
            $this->adminHelper->displayPriceAttribute($dataObject, 'code', $strong, $separator)
        );
    }

    /**
     * @return array
     */
    public function displayPricesDataProvider()
    {
        return [
            [
                '<strong>formattedBasePrice</strong><br/>[formattedPrice]',
                true,
            ],
            [
                '<strong>formattedBasePrice</strong><br/>[formattedPrice]',
                false,
            ],
            [
                'formattedPrice',
                true,
                false,
            ],
            [
                'formattedPrice',
                false,
                false,
            ],
            [
                '<strong>formattedPrice</strong>',
                true,
                false,
                true,
                true,
            ],
            [
                '<strong>formattedPrice</strong>',
                true,
                false,
                true,
                true,
                'seperator',
            ],
            [
                '<strong>formattedBasePrice</strong>seperator[formattedPrice]',
                true,
                true,
                true,
                true,
                'seperator',
            ],
            [
                'storeFormattedPrice',
                false,
                false,
                false,
                false,
                'seperator',
            ],
            [
                '<strong>storeFormattedPrice</strong>',
                false,
                false,
                false,
                true,
                'seperator',
            ],

        ];
    }

    /**
     * @param string $itemKey
     * @param string $type
     * @param int $calledTimes
     * @dataProvider applySalableProductTypesFilterDataProvider
     */
    public function testApplySalableProductTypesFilter($itemKey, $type, $calledTimes)
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn($type);
        $orderMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductType'])
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getProductType')
            ->willReturn($type);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getProductType')
            ->willReturn($type);
        $items = [
            'product' => $productMock,
            'order' => $orderMock,
            'quote' => $quoteMock,
            'other' => 'other',
        ];
        $collectionClassName = AbstractCollection::class;
        $collectionMock = $this->getMockBuilder($collectionClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$items[$itemKey]]);
        $collectionMock->expects($this->exactly($calledTimes))
            ->method('removeItemByKey');
        $this->salesConfigMock->expects($this->any())
            ->method('getAvailableProductTypes')
            ->willReturn(['validProductType']);
        $this->adminHelper->applySalableProductTypesFilter($collectionMock);
    }

    /**
     * @return array
     */
    public function applySalableProductTypesFilterDataProvider()
    {
        return [
            ['product', 'validProductType', 0],
            ['product', 'invalidProductType', 1],
            ['order', 'validProductType', 0],
            ['order', 'invalidProductType', 1],
            ['quote', 'validProductType', 0],
            ['quote', 'invalidProductType', 1],
            ['other', 'validProductType', 1],
        ];
    }

    /**
     * @return void
     */
    public function testEscapeHtmlWithLinks(): void
    {
        $expected = '&lt;a&gt;some text in tags&lt;/a&gt;';
        $this->escaperMock
            ->expects($this->any())
            ->method('escapeHtml')
            ->willReturn($expected);
        $actual = $this->adminHelper->escapeHtmlWithLinks('<a>some text in tags</a>');
        $this->assertEquals($expected, $actual);
    }
}
