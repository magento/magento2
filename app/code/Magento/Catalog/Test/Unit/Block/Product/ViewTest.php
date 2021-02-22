<?php
/**
 * Test class for \Magento\Catalog\Block\Product\View
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Product;

/**
 * Class ViewTest
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View
     */
    protected $view;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productTypeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productTypeConfig = $this->createMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->view = $helper->getObject(
            \Magento\Catalog\Block\Product\View::class,
            ['productTypeConfig' => $this->productTypeConfig, 'registry' => $this->registryMock]
        );
    }

    /**
     * @return void
     */
    public function testShouldRenderQuantity()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registryMock->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'product'
        )->willReturn(
            $productMock
        );
        $productMock->expects($this->once())->method('getTypeId')->willReturn('id');
        $this->productTypeConfig->expects(
            $this->once()
        )->method(
            'isProductSet'
        )->with(
            'id'
        )->willReturn(
            true
        );
        $this->assertFalse($this->view->shouldRenderQuantity());
    }

    /**
     * @return void
     */
    public function testGetIdentities()
    {
        $productTags = ['cat_p_1'];
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $product->expects($this->once())
            ->method('getIdentities')
            ->willReturn($productTags);
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->willReturnMap(
                
                    [
                        ['product', $product],
                    ]
                
            );
        $this->assertEquals($productTags, $this->view->getIdentities());
    }
}
