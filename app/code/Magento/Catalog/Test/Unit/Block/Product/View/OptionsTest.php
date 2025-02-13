<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

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

        $optValFactoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\ValueFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $option = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Option::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optValFactoryMock]
        );
        $dateBlock = $this->getMockBuilder(ProductOptions::class)
            ->addMethods(['setOption'])
            ->onlyMethods(['setProduct'])
            ->setConstructorArgs(['context' => $context, 'option' => $option])
            ->disableOriginalConstructor()
            ->getMock();
        $dateBlock->expects($this->any())->method('setProduct')->willReturn($dateBlock);

        $layout->expects($this->any())->method('getChildName')->willReturn('date');
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
        $dateBlock->expects(
            $this->any()
        )->method(
            'setOption'
        )->with(
            $option
        )->willReturn(
            $dateBlock
        );
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }
}
