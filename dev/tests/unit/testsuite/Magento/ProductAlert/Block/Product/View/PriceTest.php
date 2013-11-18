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
 * @package     Magento_ProductAlert
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ProductAlert\Block\Product\View;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testPrepareLayoutUrlIsSet()
    {
        $helper = $this->getMockBuilder('Magento\ProductAlert\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('isPriceAlertAllowed', 'getSaveUrl'))
            ->getMock();
        $helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue(true));
        $helper->expects($this->once())->method('getSaveUrl')->with('price')->will($this->returnValue('http://url'));

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(array('getCanShowPrice', 'getId'))
            ->getMock();
        $product->expects($this->once())->method('getId')->will($this->returnValue(1));
        $product->expects($this->once())->method('getCanShowPrice')->will($this->returnValue(true));

        $registry = $this->getMockBuilder('Magento\Core\Model\Registry')
            ->disableOriginalConstructor()
            ->setMethods(array('registry'))
            ->getMock();
        $registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $block = $this->_objectManager->getObject(
            'Magento\ProductAlert\Block\Product\View\Price',
            array(
                'helper' => $helper,
                'registry' => $registry,
            )
        );

        $layout = $this->getMockBuilder('Magento\Core\Model\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $block->setTemplate('path/to/template.phtml');
        $block->setLayout($layout);

        $this->assertEquals('path/to/template.phtml', $block->getTemplate());
        $this->assertEquals('http://url', $block->getSignupUrl());
    }

    public function testPrepareLayoutTemplateReseted()
    {
        $block = $this->_objectManager->getObject('Magento\ProductAlert\Block\Product\View\Price');
        $this->assertEquals('', $block->getTemplate());
    }
}
