<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Model\Product\Option\ValueFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionsTest extends TestCase
{
    use MockCreationTrait;
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
        $optionFactoryMock = $this->createPartialMock(ValueFactory::class, ['create']);
        $option = $this->_objectHelper->getObject(
            ProductOption::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optionFactoryMock]
        );
        
        $dateBlock = $this->createPartialMockWithReflection(
            Text::class,
            ['setProduct', 'setOption']
        );
        $dateBlock->method('setProduct')->willReturnSelf();
        $dateBlock->method('setOption')->willReturnSelf();

        $layout->method('getChildName')->willReturn('date');
        $layout->expects($this->any())->method('getBlock')->with('date')->willReturn($dateBlock);
        $layout->expects($this->any())->method('renderElement')->with('date', false)->willReturn('html');

        $this->_optionsBlock = $this->_objectHelper->getObject(
            Options::class,
            [
                'context' => $context,
                'pricingHelper' => $this->createMock(PricingHelper::class),
                'catalogData' => $this->createMock(CatalogHelper::class),
                'jsonEncoder' => $this->createMock(EncoderInterface::class),
                'option' => $option,
                'registry' => $this->createMock(Registry::class),
                'arrayUtils' => $this->createMock(ArrayUtils::class)
            ]
        );

        $itemOptFactoryMock = $this->createPartialMock(
            OptionFactory::class,
            ['create']
        );
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
            ProductOption::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optionFactoryMock]
        );
        $option->setType('date');
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }
}
