<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Test\Unit\Plugin\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProductGraphQl\Model\Cart\BuyRequest\SuperAttributeDataProvider;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ConfigurableProductGraphQl\Plugin\Quote\UpdateCustomizedOptions;
use Magento\Quote\Model\Quote as Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for data before update cart and check customized options available
 * and update super attribute data for configurable product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCustomizedOptionsTest extends TestCase
{
    /** @var UpdateCustomizedOptions */
    private $model;

    /** @var QuoteItem|MockObject */
    private $quoteItem;

    /** @var Quote|MockObject */
    private $quote;

    /** @var Product|MockObject */
    private $productMock;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var SuperAttributeDataProvider|MockObject
     */
    private $superAttributeDataProviderMock;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->superAttributeDataProviderMock = $this->getMockBuilder(SuperAttributeDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore', 'getItemById'])
            ->getMock();

        $this->quoteItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'getProductType', 'getChildren'])
            ->onlyMethods(['getQty', 'getSku'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            UpdateCustomizedOptions::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'superAttributeDataProvider' => $this->superAttributeDataProviderMock
            ]
        );
    }

    /**
     * Test buyRequest object for customized options before update
     *
     * @param array $superAttributeDetails
     * @param DataObject $buyRequest
     * @param array $itemDetails
     * @param string $productType
     * @param array $productOptions
     * @param DataObject $productChildren
     * @param bool $quoteHasItem
     * @param int|null $productId
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @dataProvider updateCustomizedOptionsDataProvider
     */
    public function testBeforeUpdateItem(
        array $superAttributeDetails,
        DataObject $buyRequest,
        array $itemDetails,
        string $productType,
        array $productOptions,
        DataObject $productChildren,
        bool $quoteHasItem = true,
        ?int $productId = null
    ) {
        $params = new DataObject([]);
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->productMock->expects($this->any())
            ->method('getSku')
            ->willReturn($itemDetails['parent_sku']);
        $this->quoteItem->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->quote->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemDetails['store_id']);

        $this->quote->expects($this->once())
            ->method('getItemById')
            ->willReturn($quoteHasItem ? $this->quoteItem : false);

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);

        $this->productMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([$productOptions]);

        $this->quoteItem->expects($this->any())
            ->method('getProductType')
            ->willReturn($productType);

        $this->quoteItem->expects($this->any())
            ->method('getChildren')
            ->willReturn([$productChildren]);

        $this->quoteItem->expects($this->any())
            ->method('getSku')
            ->willReturn($itemDetails['sku']);

        $this->superAttributeDataProviderMock->expects($this->any())
            ->method('execute')
            ->willReturn($superAttributeDetails);

        $this->model->beforeUpdateItem($this->quote, $itemDetails['item_id'], $buyRequest, $params);
    }

    /**
     * @return array
     */
    public static function updateCustomizedOptionsDataProvider()
    {
        return [
            'test customized options for simple product' => [
                [],
                new DataObject(['sku' => 'simple', 'quantity' => 1]),
                ['item_id' => 1, 'store_id' => 1, 'parent_sku' => null, 'sku' => 'simple'] ,
                Type::TYPE_SIMPLE,
                [],
                new DataObject([]),
                false,
                5
            ],
            'test customized options for configurable product' => [
                ['1' => 14, '5' => 10],
                new DataObject(['sku' => 'configurable', 'quantity' => 10]),
                ['item_id' => 2, 'store_id' => 1, 'parent_sku' => 'configurable', 'sku' => 'configurable-child'] ,
                Configurable::TYPE_CODE,
                ['option1'],
                new DataObject(['sku' => 'child1']),
                true,
                7
            ]
        ];
    }
}
