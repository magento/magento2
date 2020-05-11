<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Helper\Catalog\Product;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private $pricingHelper;

    /**
     * @var Configuration|MockObject
     */
    private $productConfiguration;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Bundle\Helper\Catalog\Product\Configuration
     */
    private $helper;

    /**
     * @var ItemInterface|MockObject
     */
    private $item;

    /**
     * @var Json
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->pricingHelper = $this->createPartialMock(Data::class, ['currency']);
        $this->productConfiguration = $this->createMock(Configuration::class);
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $this->item = $this->getMockBuilder(ItemInterface::class)
            ->addMethods(['getQty'])
            ->onlyMethods(['getProduct', 'getOptionByCode', 'getFileDownloadParams'])
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->setMethods(['unserialize'])
            ->getMockForAbstractClass();

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->helper = (new ObjectManager($this))->getObject(
            \Magento\Bundle\Helper\Catalog\Product\Configuration::class,
            [
                'pricingHelper' => $this->pricingHelper,
                'productConfiguration' => $this->productConfiguration,
                'escaper' => $this->escaper,
                'serializer' => $this->serializer
            ]
        );
    }

    public function testGetSelectionQty()
    {
        $selectionId = 15;
        $selectionQty = 35;
        $product = $this->createMock(Product::class);
        $option = $this->getMockBuilder(Option::class)
            ->addMethods(['getValue'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('selection_qty_' . $selectionId)
            ->willReturn($option);
        $option->expects($this->once())
            ->method('getValue')
            ->willReturn($selectionQty);

        $this->assertEquals($selectionQty, $this->helper->getSelectionQty($product, $selectionId));
    }

    public function testGetSelectionQtyIfCustomOptionIsNotSet()
    {
        $selectionId = 15;
        $product = $this->createMock(Product::class);

        $product->expects($this->once())->method('getCustomOption')->with('selection_qty_' . $selectionId)
            ->willReturn(null);

        $this->assertEquals(0, $this->helper->getSelectionQty($product, $selectionId));
    }

    public function testGetSelectionFinalPrice()
    {
        $itemQty = 2;

        $product = $this->createMock(Product::class);
        $price = $this->createMock(Price::class);
        $selectionProduct = $this->createMock(Product::class);

        $selectionProduct->expects($this->once())->method('unsetData')->with('final_price');
        $this->item->expects($this->once())->method('getProduct')->willReturn($product);
        $this->item->expects($this->once())->method('getQty')->willReturn($itemQty);
        $product->expects($this->once())->method('getPriceModel')->willReturn($price);
        $price->expects($this->once())->method('getSelectionFinalTotalPrice')
            ->with($product, $selectionProduct, $itemQty, 0, false, true);

        $this->helper->getSelectionFinalPrice($this->item, $selectionProduct);
    }

    public function testGetBundleOptionsEmptyBundleOptionsIds()
    {
        $typeInstance = $this->createMock(Type::class);
        $product = $this->createPartialMock(Product::class, ['getTypeInstance',
            '__wakeup']);

        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $this->item->expects($this->once())->method('getProduct')->willReturn($product);
        $this->item->expects($this->once())->method('getOptionByCode')->with('bundle_option_ids')
            ->willReturn(null);

        $this->assertEquals([], $this->helper->getBundleOptions($this->item));
    }

    public function testGetBundleOptionsEmptyBundleSelectionIds()
    {
        $optionIds = '{"0":"1"}';
        $collection = $this->createMock(Collection::class);
        $product = $this->createPartialMock(Product::class, ['getTypeInstance',
            '__wakeup']);
        $typeInstance = $this->createPartialMock(Type::class, ['getOptionsByIds']);
        $selectionOption = $this->createPartialMock(
            OptionInterface::class,
            ['getValue']
        );
        $itemOption = $this->createPartialMock(
            OptionInterface::class,
            ['getValue']
        );

        $selectionOption->expects($this->once())
            ->method('getValue')
            ->willReturn('[]');
        $itemOption->expects($this->once())
            ->method('getValue')
            ->willReturn($optionIds);
        $typeInstance->expects($this->once())
            ->method('getOptionsByIds')
            ->with(
                json_decode($optionIds, true),
                $product
            )
            ->willReturn($collection);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstance);
        $this->item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->item->expects($this->at(1))
            ->method('getOptionByCode')
            ->with('bundle_option_ids')
            ->willReturn($itemOption);
        $this->item->expects($this->at(2))
            ->method('getOptionByCode')
            ->with('bundle_selection_ids')
            ->willReturn($selectionOption);

        $this->assertEquals([], $this->helper->getBundleOptions($this->item));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetOptions()
    {
        $optionIds = '{"0":"1"}';
        $selectionIds =  '{"0":"2"}';
        $selectionId = '2';
        $product = $this->getMockBuilder(Product::class)
            ->addMethods(['getSelectionId'])
            ->onlyMethods(['getTypeInstance', '__wakeup', 'getCustomOption', 'getName', 'getPriceModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance = $this->createPartialMock(
            Type::class,
            ['getOptionsByIds', 'getSelectionsByIds']
        );
        $priceModel = $this->createPartialMock(
            Price::class,
            ['getSelectionFinalTotalPrice']
        );
        $selectionQty = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['getValue', '__wakeup']
        );
        $bundleOption = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->addMethods(['getSelections'])
            ->onlyMethods(['getTitle', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionOption = $this->createPartialMock(
            OptionInterface::class,
            ['getValue']
        );
        $collection = $this->createPartialMock(
            Collection::class,
            ['appendSelections']
        );
        $itemOption = $this->createPartialMock(
            OptionInterface::class,
            ['getValue']
        );
        $collection2 = $this->createMock(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class);

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with('name')
            ->willReturn('name');
        $this->pricingHelper->expects($this->once())->method('currency')->with(15)
            ->willReturn('<span class="price">$15.00</span>');
        $priceModel->expects($this->once())->method('getSelectionFinalTotalPrice')->willReturn(15);
        $selectionQty->expects($this->any())->method('getValue')->willReturn(1);
        $bundleOption->expects($this->any())->method('getSelections')->willReturn([$product]);
        $bundleOption->expects($this->once())->method('getTitle')->willReturn('title');
        $selectionOption->expects($this->once())->method('getValue')->willReturn($selectionIds);
        $collection->expects($this->once())->method('appendSelections')->with($collection2, true)
            ->willReturn([$bundleOption]);
        $itemOption->expects($this->once())->method('getValue')->willReturn($optionIds);
        $typeInstance->expects($this->once())
            ->method('getOptionsByIds')
            ->with(
                json_decode($optionIds, true),
                $product
            )
            ->willReturn($collection);
        $typeInstance->expects($this->once())
            ->method('getSelectionsByIds')
            ->with(json_decode($selectionIds, true), $product)
            ->willReturn($collection2);
        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $product->expects($this->any())->method('getCustomOption')->with('selection_qty_' . $selectionId)
            ->willReturn($selectionQty);
        $product->expects($this->any())->method('getSelectionId')->willReturn($selectionId);
        $product->expects($this->once())->method('getName')->willReturn('name');
        $product->expects($this->once())->method('getPriceModel')->willReturn($priceModel);
        $this->item->expects($this->any())->method('getProduct')->willReturn($product);
        $this->item->expects($this->at(1))->method('getOptionByCode')->with('bundle_option_ids')
            ->willReturn($itemOption);
        $this->item->expects($this->at(2))->method('getOptionByCode')->with('bundle_selection_ids')
            ->willReturn($selectionOption);
        $this->productConfiguration->expects($this->once())->method('getCustomOptions')->with($this->item)
            ->willReturn([0 => ['label' => 'title', 'value' => 'value']]);

        $this->assertEquals(
            [
                [
                    'label' => 'title',
                    'value' => ['1 x name <span class="price">$15.00</span>'],
                    'has_html' => true,
                ],
                ['label' => 'title', 'value' => 'value'],
            ],
            $this->helper->getOptions($this->item)
        );
    }
}
