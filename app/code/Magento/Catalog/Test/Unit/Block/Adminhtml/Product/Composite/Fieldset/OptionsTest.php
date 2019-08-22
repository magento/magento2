<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Composite\Fieldset;

/**
 * Test class for \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options
     */
    protected $_optionsBlock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option
     */
    protected $_optionResource;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_optionResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Option::class);
    }

    public function testGetOptionHtml()
    {
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', 'renderElement']
        );
        $context = $this->_objectHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['layout' => $layout]
        );
        $optionFactoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\ValueFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $option = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Option::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optionFactoryMock]
        );
        $dateBlock = $this->getMockBuilder(\Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options::class)
            ->setMethods(['setSkipJsReloadPrice'])
            ->setConstructorArgs(['context' => $context, 'option' => $option])
            ->disableOriginalConstructor()
            ->getMock();
        $dateBlock->expects($this->any())->method('setSkipJsReloadPrice')->will($this->returnValue($dateBlock));

        $layout->expects($this->any())->method('getChildName')->will($this->returnValue('date'));
        $layout->expects($this->any())->method('getBlock')->with('date')->will($this->returnValue($dateBlock));
        $layout->expects($this->any())->method('renderElement')->with('date', false)->will($this->returnValue('html'));

        $this->_optionsBlock = $this->_objectHelper->getObject(
            \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options::class,
            ['context' => $context, 'option' => $option]
        );

        $itemOptFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory::class,
            ['create']
        );
        $stockItemFactoryMock = $this->createPartialMock(
            \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class,
            ['create']
        );
        $productFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $categoryFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\CategoryFactory::class, ['create']);

        $this->_optionsBlock->setProduct(
            $this->_objectHelper->getObject(
                \Magento\Catalog\Model\Product::class,
                [
                    'collectionFactory' => $this->createMock(\Magento\Framework\Data\CollectionFactory::class),
                    'itemOptionFactory' => $itemOptFactoryMock,
                    'stockItemFactory' => $stockItemFactoryMock,
                    'productFactory' => $productFactoryMock,
                    'categoryFactory' => $categoryFactoryMock
                ]
            )
        );

        $option = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Option::class,
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optionFactoryMock]
        );
        $option->setType('date');
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }
}
