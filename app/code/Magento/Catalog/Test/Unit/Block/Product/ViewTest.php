<?php declare(strict_types=1);
/**
 * Test class for \Magento\Catalog\Block\Product\View
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var MockObject
     */
    protected $productTypeConfig;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->productTypeConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->view = $helper->getObject(
            View::class,
            ['productTypeConfig' => $this->productTypeConfig, 'registry' => $this->registryMock]
        );
    }

    /**
     * @return void
     */
    public function testShouldRenderQuantity()
    {
        $productMock = $this->createMock(Product::class);
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
        $product = $this->createMock(Product::class);

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
