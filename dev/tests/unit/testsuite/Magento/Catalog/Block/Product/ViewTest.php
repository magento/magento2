<?php
/**
 * Test class for \Magento\Catalog\Block\Product\View
 *
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
namespace Magento\Catalog\Block\Product;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View
     */
    protected $view;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->productTypeConfig = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->view = $helper->getObject(
            'Magento\Catalog\Block\Product\View',
            array('productTypeConfig' => $this->productTypeConfig, 'registry' => $this->registryMock)
        );
    }

    public function testShouldRenderQuantity()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->registryMock->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'product'
        )->will(
            $this->returnValue($productMock)
        );
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('id'));
        $this->productTypeConfig->expects(
            $this->once()
        )->method(
            'isProductSet'
        )->with(
            'id'
        )->will(
            $this->returnValue(true)
        );
        $this->assertEquals(false, $this->view->shouldRenderQuantity());
    }

    public function testGetIdentities()
    {
        $productTags = array('catalog_product_1');
        $product = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $category = $this->getMock('Magento\Catalog\Model\Category', array(), array(), '', false);

        $product->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($productTags));
        $category->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->will($this->returnValueMap(
                [
                    ['product', $product],
                    ['current_category', $category]
                ]
            )
        );
        $this->assertEquals(array('catalog_product_1', 'catalog_category_product_1'), $this->view->getIdentities());
    }
}
