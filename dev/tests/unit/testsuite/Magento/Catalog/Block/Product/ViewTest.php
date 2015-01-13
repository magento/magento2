<?php
/**
 * Test class for \Magento\Catalog\Block\Product\View
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->view = $helper->getObject(
            'Magento\Catalog\Block\Product\View',
            ['productTypeConfig' => $this->productTypeConfig, 'registry' => $this->registryMock]
        );
    }

    public function testShouldRenderQuantity()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
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
        $productTags = ['catalog_product_1'];
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);

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
                    ['current_category', $category],
                ]
            )
        );
        $this->assertEquals(['catalog_product_1', 'catalog_category_product_1'], $this->view->getIdentities());
    }
}
