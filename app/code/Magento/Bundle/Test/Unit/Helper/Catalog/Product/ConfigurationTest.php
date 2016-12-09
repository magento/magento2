<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Helper\Catalog\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Pricing\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $pricingHelper;

    /** @var \Magento\Catalog\Helper\Product\Configuration|\PHPUnit_Framework_MockObject_MockObject */
    protected $productConfiguration;

    /** @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject */
    protected $escaper;

    /** @var \Magento\Bundle\Helper\Catalog\Product\Configuration */
    protected $helper;

    /** @var \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $item;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    protected function setUp()
    {
        $this->pricingHelper = $this->getMock(
            \Magento\Framework\Pricing\Helper\Data::class,
            ['currency'],
            [],
            '',
            false
        );
        $this->productConfiguration = $this->getMock(
            \Magento\Catalog\Helper\Product\Configuration::class,
            [],
            [],
            '',
            false
        );
        $this->escaper = $this->getMock(\Magento\Framework\Escaper::class, ['escapeHtml'], [], '', false);
        $this->item = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class,
            ['getQty', 'getProduct', 'getOptionByCode', 'getFileDownloadParams']
        );
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->getMockForAbstractClass();

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                });

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
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $option = $this->getMock(\Magento\Catalog\Model\Product\Option::class, ['__wakeup', 'getValue'], [], '', false);

        $product->expects($this->once())->method('getCustomOption')->with('selection_qty_' . $selectionId)
            ->will($this->returnValue($option));
        $option->expects($this->once())->method('getValue')->will($this->returnValue($selectionQty));

        $this->assertEquals($selectionQty, $this->helper->getSelectionQty($product, $selectionId));
    }

    public function testGetSelectionQtyIfCustomOptionIsNotSet()
    {
        $selectionId = 15;
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $product->expects($this->once())->method('getCustomOption')->with('selection_qty_' . $selectionId)
            ->will($this->returnValue(null));

        $this->assertEquals(0, $this->helper->getSelectionQty($product, $selectionId));
    }

    /**
     * @covers \Magento\Bundle\Helper\Catalog\Product\Configuration::getSelectionFinalPrice
     */
    public function testGetSelectionFinalPrice()
    {
        $itemQty = 2;

        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $price = $this->getMock(\Magento\Bundle\Model\Product\Price::class, [], [], '', false);
        $selectionProduct = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

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
        $typeInstance = $this->getMock(\Magento\Bundle\Model\Product\Type::class, [], [], '', false);
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeInstance',
            '__wakeup'],
            [],
            '',
            false
        );

        $product->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $this->item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->item->expects($this->once())->method('getOptionByCode')->with('bundle_option_ids')
            ->will($this->returnValue(null));

        $this->assertEquals([], $this->helper->getBundleOptions($this->item));
    }

    public function testGetBundleOptionsEmptyBundleSelectionIds()
    {
        $optionIds = 'a:1:{i:0;i:1;}';

        $collection = $this->getMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class, [], [], '', false);
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeInstance',
            '__wakeup'],
            [],
            '',
            false
        );
        $typeInstance = $this->getMock(\Magento\Bundle\Model\Product\Type::class, ['getOptionsByIds'], [], '', false);
        $selectionOption =
            $this->getMock(
                \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
                ['getValue']
            );
        $itemOption =
            $this->getMock(
                \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
                ['getValue']
            );

        $selectionOption->expects($this->once())->method('getValue')->will($this->returnValue(''));
        $itemOption->expects($this->once())->method('getValue')->will($this->returnValue($optionIds));
        $typeInstance->expects($this->once())->method('getOptionsByIds')->with(unserialize($optionIds), $product)
            ->will($this->returnValue($collection));
        $product->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $this->item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->item->expects($this->at(1))->method('getOptionByCode')->with('bundle_option_ids')
            ->will($this->returnValue($itemOption));
        $this->item->expects($this->at(2))->method('getOptionByCode')->with('bundle_selection_ids')
            ->will($this->returnValue($selectionOption));

        $this->assertEquals([], $this->helper->getBundleOptions($this->item));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetOptions()
    {
        $optionIds = 'a:1:{i:0;i:1;}';
        $selectionIds =  '{"0":"2"}';
        $selectionId = '2';
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeInstance', '__wakeup', 'getCustomOption', 'getSelectionId', 'getName', 'getPriceModel'],
            [],
            '',
            false
        );
        $typeInstance = $this->getMock(
            \Magento\Bundle\Model\Product\Type::class,
            ['getOptionsByIds', 'getSelectionsByIds'],
            [],
            '',
            false
        );
        $priceModel =
            $this->getMock(\Magento\Bundle\Model\Product\Price::class, ['getSelectionFinalTotalPrice'], [], '', false);
        $selectionQty =
            $this->getMock(\Magento\Quote\Model\Quote\Item\Option::class, ['getValue', '__wakeup'], [], '', false);
        $bundleOption =
            $this->getMock(
                \Magento\Bundle\Model\Option::class,
                ['getSelections',
                'getTitle',
                '__wakeup'],
                [],
                '',
                false
            );
        $selectionOption =
            $this->getMock(
                \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
                ['getValue']
            );
        $collection =
            $this->getMock(
                \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
                ['appendSelections'],
                [],
                '',
                false
            );
        $itemOption =
            $this->getMock(
                \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
                ['getValue']
            );
        $collection2 = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->escaper->expects($this->once())->method('escapeHtml')->with('name')->will($this->returnValue('name'));
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
        $typeInstance->expects($this->once())->method('getOptionsByIds')->with(unserialize($optionIds), $product)
            ->will($this->returnValue($collection));
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
