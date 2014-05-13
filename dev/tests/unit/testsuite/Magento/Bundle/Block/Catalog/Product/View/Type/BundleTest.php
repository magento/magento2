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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    protected $_bundleBlock;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_bundleBlock = $objectHelper->getObject('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle');
    }

    public function testGetOptionHtmlNoRenderer()
    {
        $option = $this->getMock('\Magento\Bundle\Model\Option', array('getType', '__wakeup'), array(), '', false);
        $option->expects($this->exactly(2))->method('getType')->will($this->returnValue('checkbox'));

        $this->assertEquals(
            'There is no defined renderer for "checkbox" option type.',
            $this->_bundleBlock->getOptionHtml($option)
        );
    }

    public function testGetOptionHtml()
    {
        $option = $this->getMock('\Magento\Bundle\Model\Option', array('getType', '__wakeup'), array(), '', false);
        $option->expects($this->exactly(1))->method('getType')->will($this->returnValue('checkbox'));

        $optionBlock = $this->getMock(
            '\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox',
            array('setOption', 'toHtml'),
            array(),
            '',
            false
        );
        $optionBlock->expects($this->any())->method('setOption')->will($this->returnValue($optionBlock));
        $optionBlock->expects($this->any())->method('toHtml')->will($this->returnValue('option html'));
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            array('getChildName', 'getBlock'),
            array(),
            '',
            false
        );
        $layout->expects($this->any())->method('getChildName')->will($this->returnValue('name'));
        $layout->expects($this->any())->method('getBlock')->will($this->returnValue($optionBlock));
        $this->_bundleBlock->setLayout($layout);

        $this->assertEquals('option html', $this->_bundleBlock->getOptionHtml($option));
    }
}
