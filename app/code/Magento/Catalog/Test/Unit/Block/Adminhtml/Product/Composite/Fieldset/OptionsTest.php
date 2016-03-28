<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Composite\Fieldset;

/**
 * Test class for \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
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
        $this->_optionResource = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Option',
            [],
            [],
            '',
            false
        );
    }

    public function testGetOptionHtml()
    {
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getChildName', 'getBlock', 'renderElement'],
            [],
            '',
            false
        );
        $context = $this->_objectHelper->getObject(
            'Magento\Framework\View\Element\Template\Context',
            ['layout' => $layout]
        );
        $optionFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option\ValueFactory',
            ['create'],
            [],
            '',
            false
        );

        $option = $this->_objectHelper->getObject(
            'Magento\Catalog\Model\Product\Option',
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optionFactoryMock]
        );
        $dateBlock = $this->getMock(
            'Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options',
            ['setSkipJsReloadPrice'],
            ['context' => $context, 'option' => $option],
            '',
            false
        );
        $dateBlock->expects($this->any())->method('setSkipJsReloadPrice')->will($this->returnValue($dateBlock));

        $layout->expects($this->any())->method('getChildName')->will($this->returnValue('date'));
        $layout->expects($this->any())->method('getBlock')->with('date')->will($this->returnValue($dateBlock));
        $layout->expects($this->any())->method('renderElement')->with('date', false)->will($this->returnValue('html'));

        $this->_optionsBlock = $this->_objectHelper->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Options',
            ['context' => $context, 'option' => $option]
        );

        $itemOptFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\Configuration\Item\OptionFactory',
            ['create'],
            [],
            '',
            false
        );
        $stockItemFactoryMock = $this->getMock(
            'Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $productFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $categoryFactoryMock = $this->getMock(
            'Magento\Catalog\Model\CategoryFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_optionsBlock->setProduct(
            $this->_objectHelper->getObject(
                'Magento\Catalog\Model\Product',
                [
                    'collectionFactory' => $this->getMock(
                        'Magento\Framework\Data\CollectionFactory',
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
            'Magento\Catalog\Model\Product\Option',
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optionFactoryMock]
        );
        $option->setType('date');
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }
}
