<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_optionResource = $this->getMock('Magento\Catalog\Model\Resource\Product\Option',
            array(), array(), '', false);
    }

    public function testGetOptionHtml()
    {
        $layout = $this->getMock('Magento\Core\Model\Layout', array('getChildName', 'getBlock', 'renderElement'),
            array(), '', false);
        $context = $this->_objectHelper->getObject('Magento\View\Element\Template\Context', array(
            'layout' => $layout
        ));

        $optValFactoryMock = $this->getMock('Magento\Catalog\Model\Product\Option\ValueFactory', array(),
            array(), '', false);
        $option = $this->_objectHelper->getObject('Magento\Catalog\Model\Product\Option', array(
            'resource' => $this->_optionResource,
            'optionValueFactory' => $optValFactoryMock,
        ));
        $dateBlock = $this->getMock('Magento\Backend\Block\Catalog\Product\Composite\Fieldset\Options',
            array('setProduct', 'setOption'), array('context' => $context, 'option' => $option), '', false);
        $dateBlock->expects($this->any())
            ->method('setProduct')->will($this->returnValue($dateBlock));

        $layout->expects($this->any())
            ->method('getChildName')->will($this->returnValue('date'));
        $layout->expects($this->any())
            ->method('getBlock')->with('date')->will($this->returnValue($dateBlock));
        $layout->expects($this->any())
            ->method('renderElement')->with('date', false)->will($this->returnValue('html'));

        $this->_optionsBlock = $this->_objectHelper->getObject(
            'Magento\Catalog\Block\Product\View\Options',
            array(
                'context' => $context,
                'option' => $option,
            )
        );

        $itemOptFactoryMock = $this->getMock('Magento\Catalog\Model\Product\Configuration\Item\OptionFactory',
            array('create'), array(), '', false);
        $stockItemFactoryMock = $this->getMock('Magento\CatalogInventory\Model\Stock\ItemFactory',
            array('create'), array(), '', false);
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory',
            array('create'), array(), '', false);
        $categoryFactoryMock = $this->getMock('Magento\Catalog\Model\CategoryFactory',
            array('create'), array(), '', false);
        $this->_optionsBlock->setProduct(
            $this->_objectHelper->getObject(
                'Magento\Catalog\Model\Product',
                array(
                    'collectionFactory' => $this->getMock(
                        'Magento\Data\CollectionFactory',
                        array(),
                        array(),
                        '',
                        false
                    ),
                    'itemOptionFactory' => $itemOptFactoryMock,
                    'stockItemFactory' => $stockItemFactoryMock,
                    'productFactory' => $productFactoryMock,
                    'categoryFactory' => $categoryFactoryMock,
                )
            )
        );

        $option = $this->_objectHelper->getObject('Magento\Catalog\Model\Product\Option',
            array('resource' => $this->_optionResource)
        );
        $option->setType('date');
        $dateBlock->expects($this->any())
            ->method('setOption')->with($this->equalTo($option))->will($this->returnValue($dateBlock));
        $this->assertEquals('html', $this->_optionsBlock->getOptionHtml($option));
    }
}
