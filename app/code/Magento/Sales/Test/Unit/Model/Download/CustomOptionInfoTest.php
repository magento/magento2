<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Download;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product\OptionFactory as ProductOptionFactory;
use Magento\Quote\Model\Quote\Item\OptionFactory as QuoteItemOptionFactory;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Download\CustomOptionInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomOptionInfoTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $orderItemRepository;

    /**
     * @var MockObject
     */
    protected $productOptionFactory;

    /**
     * @var MockObject
     */
    protected $quoteItemOptionFactory;

    /**
     * @var MockObject
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $json;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->orderItemRepository = $this->getMockBuilder(ItemRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $this->productOptionFactory = $this->getMockBuilder(ProductOptionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->quoteItemOptionFactory = $this->getMockBuilder(QuoteItemOptionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSearchCustomOptionByQuoteItemId() : void
    {
        $quoteItemid = 123;
        $orderItemId = 0;
        $optionId = 0;

        $customOptionInfoModel = $this->objectManager->getObject(
            CustomOptionInfo::class,
            [
                'orderItemRepository' => $this->orderItemRepository,
                'productOptionFactory' => $this->productOptionFactory,
                'quoteItemOptionFactory' => $this->quoteItemOptionFactory,
                'serializer' => $this->serializer
            ]
        );

        $quoteItemOption = $this->getMockBuilder(QuoteItemOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getValue'])
            ->addMethods(['getCode'])
            ->getMock();

        $productOption = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getType'])
            ->getMock();

        $this->quoteItemOptionFactory->expects($this->once())->method('create')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('load')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('getId')->willReturn(123);
        $quoteItemOption->expects($this->any())->method('getCode')->willReturn('option_2');

        $this->productOptionFactory->expects($this->once())->method('create')->willReturn($productOption);
        $productOption->expects($this->once())->method('load')->willReturn($productOption);
        $productOption->expects($this->once())->method('getId')->willReturn(1234);
        $productOption->expects($this->once())->method('getType')->willReturn('file');

        $json='
        {
            "type": "image\/jpeg",
            "title": "image001.jpg",
            "quote_path": "custom_options\/quote\/A\/A\/AAAAA",
            "order_path": "custom_options\/order\/A\/A\/AAAAA",
            "fullpath": "/pub\/media\/custom_options\/quote\/A\/A\/AAAAAA",
            "size": "14478",
            "width": 426,
            "height": 288,
            "secret_key": "AAAAA"
        }';

        $quoteItemOption->expects($this->any())->method('getValue')->willReturn($json);

        $this->serializer->expects($this->once())->method('unserialize')->willReturn(json_decode($json));

        $customOptionInfoModel->search($quoteItemid, $orderItemId, $optionId);
    }

    public function testSearchCustomOptionByOrder() : void
    {

        $quoteItemid = 0;
        $orderItemId = 123;
        $optionId = 1234;

        $customOptionInfoModel = $this->objectManager->getObject(
            CustomOptionInfo::class,
            [
                'orderItemRepository' => $this->orderItemRepository,
                'productOptionFactory' => $this->productOptionFactory,
                'quoteItemOptionFactory' => $this->quoteItemOptionFactory,
                'serializer' => $this->serializer
            ]
        );

        $json='
        {
            "info_buyRequest": {
                "uenc": "AAAAA,,",
                "product": "1",
                "selected_configurable_option": "",
                "related_product": "",
                "item": "1",
                "qty": "1",
                "options": {
                    "2": {
                        "type": "image\/png",
                        "title": "image.png",
                        "quote_path": "custom_options\/quote\/A\/a\/AAAAA",
                        "order_path": "custom_options\/order\/A\/a\/AAAAA",
                        "fullpath": "/pub\/media\/custom_options\/quote\/A\/a\/AAAAA",
                        "size": "315404",
                        "width": 400,
                        "height": 532,
                        "secret_key": "AAAAA"
                    }
                }
            }
        }';

        $productOption = json_decode($json, true);

        $orderItem = $this->getMockBuilder(OrderItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductOptions'])
            ->getMock();

        $this->orderItemRepository->expects($this->once())->method('get')->willReturn($orderItem);
        $orderItem->expects($this->once())->method('getProductOptions')->willReturn($productOption);

        $customOptionInfoModel->search($quoteItemid, $orderItemId, $optionId);
    }

    public function testSearchCustomOptionWithNoQuoteItemId() : void
    {
        $quoteItemid = 0;
        $orderItemId = 0;
        $optionId = 0;

        $customOptionInfoModel = $this->objectManager->getObject(
            CustomOptionInfo::class,
            [
                'orderItemRepository' => $this->orderItemRepository,
                'productOptionFactory' => $this->productOptionFactory,
                'quoteItemOptionFactory' => $this->quoteItemOptionFactory,
                'serializer' => $this->serializer
            ]
        );

        $quoteItemOption = $this->getMockBuilder(QuoteItemOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId'])
            ->getMock();

        $this->quoteItemOptionFactory->expects($this->once())
            ->method('create')
            ->willReturn($quoteItemOption);

        $quoteItemOption->expects($this->once())->method('load')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('getId')->willReturn(null);
        $this->expectException(NoSuchEntityException::class);

        $customOptionInfoModel->search($quoteItemid, $orderItemId, $optionId);
    }

    public function testSearchCustomOptionWithNoProductOption() : void
    {
        $quoteItemid = 123;
        $orderItemId = 0;
        $optionId = 0;

        $customOptionInfoModel = $this->objectManager->getObject(
            CustomOptionInfo::class,
            [
                'orderItemRepository' => $this->orderItemRepository,
                'productOptionFactory' => $this->productOptionFactory,
                'quoteItemOptionFactory' => $this->quoteItemOptionFactory,
                'serializer' => $this->serializer
            ]
        );

        $quoteItemOption = $this->getMockBuilder(QuoteItemOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getValue'])
            ->addMethods(['getCode'])
            ->getMock();

        $productOption = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getType'])
            ->getMock();

        $this->quoteItemOptionFactory->expects($this->once())->method('create')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('load')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('getId')->willReturn(123);
        $quoteItemOption->expects($this->any())->method('getCode')->willReturn('option_2');

        $this->productOptionFactory->expects($this->once())->method('create')->willReturn($productOption);
        $productOption->expects($this->once())->method('load')->willReturn($productOption);
        $productOption->expects($this->once())->method('getId')->willReturn(null);
        $this->expectException(NoSuchEntityException::class);

        $customOptionInfoModel->search($quoteItemid, $orderItemId, $optionId);
    }

    public function testSearchCustomOptionWithDifferentType() : void
    {
        $quoteItemid = 123;
        $orderItemId = 0;
        $optionId = 0;

        $customOptionInfoModel = $this->objectManager->getObject(
            CustomOptionInfo::class,
            [
                'orderItemRepository' => $this->orderItemRepository,
                'productOptionFactory' => $this->productOptionFactory,
                'quoteItemOptionFactory' => $this->quoteItemOptionFactory,
                'serializer' => $this->serializer
            ]
        );

        $quoteItemOption = $this->getMockBuilder(QuoteItemOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getValue'])
            ->addMethods(['getCode'])
            ->getMock();

        $productOption = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getType'])
            ->getMock();

        $this->quoteItemOptionFactory->expects($this->once())->method('create')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('load')->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())->method('getId')->willReturn(123);
        $quoteItemOption->expects($this->any())->method('getCode')->willReturn('option_2');

        $this->productOptionFactory->expects($this->once())->method('create')->willReturn($productOption);
        $productOption->expects($this->once())->method('load')->willReturn($productOption);
        $productOption->expects($this->once())->method('getId')->willReturn(123);
        $productOption->expects($this->once())->method('getType')->willReturn('int');
        $this->expectException(LocalizedException::class);

        $customOptionInfoModel->search($quoteItemid, $orderItemId, $optionId);
    }
}
