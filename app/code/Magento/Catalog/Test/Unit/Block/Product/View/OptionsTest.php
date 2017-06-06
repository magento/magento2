<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

/**
 * Test class for \Magento\Catalog\Block\Product\View\Options
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Catalog\Block\Product\View\Options
     */
    protected $_optionsBlock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option
     */
    protected $_optionResource;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_optionResource = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option::class,
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetOptionHtml()
    {
        $layout = $this->getMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', 'renderElement'],
            [],
            '',
            false
        );
        $context = $this->_objectHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['layout' => $layout]
        );

        $optValFactoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\Option\ValueFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $option = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Option::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optValFactoryMock]
        );
        $dateBlock = $this->getMock(
            \Magento\Backend\Block\Catalog\Product\Composite\Fieldset\Options::class,
            ['setProduct', 'setOption'],
            ['context' => $context, 'option' => $option],
            '',
            false
        );
        $dateBlock->expects($this->any())->method('setProduct')->will($this->returnValue($dateBlock));

        $layout->expects($this->any())->method('getChildName')->will($this->returnValue('date'));
        $layout->expects($this->any())->method('getBlock')->with('date')->will($this->returnValue($dateBlock));
        $layout->expects($this->any())->method('renderElement')->with('date', false)->will($this->returnValue('html'));

        $this->_optionsBlock = $this->_objectHelper->getObject(
            \Magento\Catalog\Block\Product\View\Options::class,
            ['context' => $context, 'option' => $option]
        );

        $itemOptFactoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $stockItemFactoryMock = $this->getMock(
            \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $productFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $categoryFactoryMock = $this->getMock(
            \Magento\Catalog\Model\CategoryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_optionsBlock->setProduct(
            $this->_objectHelper->getObject(
                \Magento\Catalog\Model\Product::class,
                [
                    'collectionFactory' => $this->getMock(
                        \Magento\Framework\Data\CollectionFactory::class,
                        [],
                        [],
                        '',
                        false
                    ),
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
            $this->equalTo($option)
        )->will(
            $this->returnValue($dateBlock)
        );
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }
}
