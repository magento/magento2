<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View;

/**
 * Test class for \Magento\Catalog\Block\Product\View\Options
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Catalog\Block\Product\View\Options
     */
    protected $_optionsBlock;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Option
     */
    protected $_optionResource;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_optionResource = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Option',
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

        $optValFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option\ValueFactory',
            [],
            [],
            '',
            false
        );
        $option = $this->_objectHelper->getObject(
            'Magento\Catalog\Model\Product\Option',
            ['resource' => $this->_optionResource, 'optionValueFactory' => $optValFactoryMock]
        );
        $dateBlock = $this->getMock(
            'Magento\Backend\Block\Catalog\Product\Composite\Fieldset\Options',
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
            'Magento\Catalog\Block\Product\View\Options',
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
            'Magento\CatalogInventory\Model\Stock\ItemFactory',
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
