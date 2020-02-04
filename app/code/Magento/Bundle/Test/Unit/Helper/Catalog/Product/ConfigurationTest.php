<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Helper\Catalog\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productConfiguration;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Bundle\Helper\Catalog\Product\Configuration
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    protected function setUp()
    {
        $this->pricingHelper = $this->createPartialMock(\Magento\Framework\Pricing\Helper\Data::class, ['currency']);
        $this->productConfiguration = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);
        $this->escaper = $this->createPartialMock(\Magento\Framework\Escaper::class, ['escapeHtml']);
        $this->item = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class,
            ['getQty', 'getProduct', 'getOptionByCode', 'getFileDownloadParams']
        );
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $option = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, ['__wakeup', 'getValue']);

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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $product->expects($this->once())->method('getCustomOption')->with('selection_qty_' . $selectionId)
            ->will($this->returnValue(null));

        $this->assertEquals(0, $this->helper->getSelectionQty($product, $selectionId));
    }

    public function testGetSelectionFinalPrice()
    {
        $itemQty = 2;

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $price = $this->createMock(\Magento\Bundle\Model\Product\Price::class);
        $selectionProduct = $this->createMock(\Magento\Catalog\Model\Product::class);

        $selectionProduct->expects($this->once())->method('unsetData')->with('final_price');
        $this->item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->item->expects($this->once())->method('getQty')->will($this->returnValue($itemQty));
        $product->expects($this->once())->method('getPriceModel')->will($this->returnValue($price));
        $price->expects($this->once())->method('getSelectionFinalTotalPrice')
            ->with($product, $selectionProduct, $itemQty, 0, false, true);

        $this->helper->getSelectionFinalPrice($this->item, $selectionProduct);
    }

    public function testGetBundleOptionsEmptyBundleOptionsIds()
    {
        $typeInstance = $this->createMock(\Magento\Bundle\Model\Product\Type::class);
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeInstance',
            '__wakeup']);

        $product->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $this->item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->item->expects($this->once())->method('getOptionByCode')->with('bundle_option_ids')
            ->will($this->returnValue(null));

        $this->assertEquals([], $this->helper->getBundleOptions($this->item));
    }

    public function testGetBundleOptionsEmptyBundleSelectionIds()
    {
        $optionIds = '{"0":"1"}';
        $collection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeInstance',
            '__wakeup']);
        $typeInstance = $this->createPartialMock(\Magento\Bundle\Model\Product\Type::class, ['getOptionsByIds']);
        $selectionOption = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
            ['getValue']
        );
        $itemOption = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
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
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeInstance', '__wakeup', 'getCustomOption', 'getSelectionId', 'getName', 'getPriceModel']
        );
        $typeInstance = $this->createPartialMock(
            \Magento\Bundle\Model\Product\Type::class,
            ['getOptionsByIds', 'getSelectionsByIds']
        );
        $priceModel = $this->createPartialMock(
            \Magento\Bundle\Model\Product\Price::class,
            ['getSelectionFinalTotalPrice']
        );
        $selectionQty = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['getValue', '__wakeup']
        );
        $bundleOption = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            [
                'getSelections',
                'getTitle',
                '__wakeup'
            ]
        );
        $selectionOption = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
            ['getValue']
        );
        $collection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            ['appendSelections']
        );
        $itemOption = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
            ['getValue']
        );
        $collection2 = $this->createMock(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class);

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with('name')
            ->willReturn('name');
        $this->pricingHelper->expects($this->once())->method('currency')->with(15)
            ->will($this->returnValue('<span class="price">$15.00</span>'));
        $priceModel->expects($this->once())->method('getSelectionFinalTotalPrice')->will($this->returnValue(15));
        $selectionQty->expects($this->any())->method('getValue')->will($this->returnValue(1));
        $bundleOption->expects($this->any())->method('getSelections')->will($this->returnValue([$product]));
        $bundleOption->expects($this->once())->method('getTitle')->will($this->returnValue('title'));
        $selectionOption->expects($this->once())->method('getValue')->will($this->returnValue($selectionIds));
        $collection->expects($this->once())->method('appendSelections')->with($collection2, true)
            ->will($this->returnValue([$bundleOption]));
        $itemOption->expects($this->once())->method('getValue')->will($this->returnValue($optionIds));
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
            ->will($this->returnValue($collection2));
        $product->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $product->expects($this->any())->method('getCustomOption')->with('selection_qty_' . $selectionId)
            ->will($this->returnValue($selectionQty));
        $product->expects($this->any())->method('getSelectionId')->will($this->returnValue($selectionId));
        $product->expects($this->once())->method('getName')->will($this->returnValue('name'));
        $product->expects($this->once())->method('getPriceModel')->will($this->returnValue($priceModel));
        $this->item->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        $this->item->expects($this->at(1))->method('getOptionByCode')->with('bundle_option_ids')
            ->will($this->returnValue($itemOption));
        $this->item->expects($this->at(2))->method('getOptionByCode')->with('bundle_selection_ids')
            ->will($this->returnValue($selectionOption));
        $this->productConfiguration->expects($this->once())->method('getCustomOptions')->with($this->item)
            ->will($this->returnValue([0 => ['label' => 'title', 'value' => 'value']]));

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
