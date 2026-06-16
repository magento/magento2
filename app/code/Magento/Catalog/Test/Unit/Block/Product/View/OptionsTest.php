<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Model\Product\Option\ValueFactory;
use Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options as ProductOptions;
use Magento\Catalog\Block\Product\View\Options;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Block\Product\View\Options
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var Options
     */
    protected $_optionsBlock;

    /**
     * @var Option
     */
    protected $_optionResource;

    protected function setUp(): void
    {
        $this->_objectHelper = new ObjectManager($this);
        $this->_optionResource = $this->createMock(Option::class);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetOptionHtml()
    {
        $layout = $this->createPartialMock(
            Layout::class,
            ['getChildName', 'getBlock', 'renderElement']
        );
        $context = $this->_objectHelper->getObject(
            Context::class,
            ['layout' => $layout]
        );

        $optValFactoryMock = $this->createPartialMock(ValueFactory::class, ['create']);
        $option = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Option::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optValFactoryMock]
        );
        $dateBlock = $this->createPartialMock(ProductOptions::class, ['toHtml']);
        $dateBlock->method('toHtml')->willReturn('');

        $layout->method('getChildName')->willReturn('date');
        $layout->expects($this->any())->method('getBlock')->with('date')->willReturn($dateBlock);
        $layout->expects($this->any())->method('renderElement')->with('date', false)->willReturn('html');

        $this->_optionsBlock = $this->_objectHelper->getObject(
            Options::class,
            ['context' => $context, 'option' => $option]
        );

        $itemOptFactoryMock = $this->createPartialMock(OptionFactory::class, ['create']);
        $stockItemFactoryMock = $this->createPartialMock(
            StockItemInterfaceFactory::class,
            ['create']
        );
        $productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $categoryFactoryMock = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->_optionsBlock->setProduct(
            $this->_objectHelper->getObject(
                Product::class,
                [
                    'collectionFactory' => $this->createMock(CollectionFactory::class),
                    'itemOptionFactory' => $itemOptFactoryMock,
                    'stockItemFactory' => $stockItemFactoryMock,
                    'productFactory' => $productFactoryMock,
                    'categoryFactory' => $categoryFactoryMock
                ]
            )
        );

        $option = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Option::class,
            ['resource' => $this->_optionResource]
        );
        $option->setType('date');
        // The anonymous class already implements setOption to return $this
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }

    /**
     * @return void
     */
    public function testGetJsonConfig()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $optionMock = $this->createMock(\Magento\Catalog\Model\Product\Option::class);

        $productMock->method('getOptions')->willReturn([$optionMock]);

        $optionMock->method('getId')->willReturn(1);
        $optionMock->method('hasValues')->willReturn(false);
        $optionMock->method('getPrice')->with(true)->willReturn(35.67);
        $optionMock->method('getRegularPrice')->willReturn(35.67);
        $optionMock->method('getPriceType')->willReturn('fixed');
        $optionMock->method('getProduct')->willReturn($productMock);

        $pricingHelperMock = $this->createMock(\Magento\Framework\Pricing\Helper\Data::class);
        $pricingHelperMock->method('currency')->with(35.67, false, false)->willReturn(17.842134);

        $catalogDataMock = $this->createMock(\Magento\Catalog\Helper\Data::class);
        $catalogDataMock->method('getTaxPrice')
            ->willReturnMap([
                [$productMock, 17.842134, true, null, null, null, null, null, false, 17.842134],
                [$productMock, 17.842134, false, null, null, null, null, null, false, 17.842134]
            ]);

        $jsonEncoderMock = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);
        $jsonEncoderMock->method('encode')->willReturnCallback('json_encode');

        $optionsBlock = $this->_objectHelper->getObject(
            Options::class,
            [
                'pricingHelper' => $pricingHelperMock,
                'catalogData' => $catalogDataMock,
                'jsonEncoder' => $jsonEncoderMock,
            ]
        );
        $optionsBlock->setProduct($productMock);

        $expectedConfig = [
            1 => [
                'prices' => [
                    'oldPrice' => [
                        'amount' => 17.842134,
                        'adjustments' => []
                    ],
                    'basePrice' => [
                        'amount' => 17.842134
                    ],
                    'finalPrice' => [
                        'amount' => 17.842134
                    ]
                ],
                'type' => 'fixed',
                'name' => null
            ]
        ];

        $jsonConfig = $optionsBlock->getJsonConfig();
        $this->assertEquals(json_encode($expectedConfig), $jsonConfig);
    }
}
