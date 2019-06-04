<?php
/**
 * Test class for \Magento\Catalog\Block\Product\View
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Block\Product;

class ViewTest extends \PHPUnit\Framework\TestCase
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
        $this->productTypeConfig = $this->createMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->view = $helper->getObject(
            \Magento\Catalog\Block\Product\View::class,
            ['productTypeConfig' => $this->productTypeConfig, 'registry' => $this->registryMock]
        );
    }

    public function testShouldRenderQuantity()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $product->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($productTags));
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->will($this->returnValueMap(
                [
                    ['product', $product],
                ]
            )
        );
        $this->assertEquals($productTags, $this->view->getIdentities());
    }
}
