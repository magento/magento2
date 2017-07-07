<?php
/**
 * Test class for \Magento\Catalog\Block\Product\View
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Block\Product;

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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productTypeConfig = $this->getMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->view = $helper->getObject(
            \Magento\Catalog\Block\Product\View::class,
            ['productTypeConfig' => $this->productTypeConfig, 'registry' => $this->registryMock]
        );
    }

    public function testShouldRenderQuantity()
    {
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
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
        $productTags = ['cat_p_1'];
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $category = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);

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
        $this->assertEquals(['cat_p_1', 'cat_c_1'], $this->view->getIdentities());
    }
}
