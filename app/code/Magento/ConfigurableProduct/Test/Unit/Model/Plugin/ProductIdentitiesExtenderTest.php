<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\ConfigurableProduct\Model\Plugin\ProductIdentitiesExtender;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

/**
 * Class ProductIdentitiesExtenderTest
 */
class ProductIdentitiesExtenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Configurable
     */
    private $configurableTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductRepositoryInterface
     */
    private $productRepositoryMock;

    /**
     * @var ProductIdentitiesExtender
     */
    private $plugin;

    protected function setUp()
    {
        $this->configurableTypeMock = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMock();

        $this->plugin = new ProductIdentitiesExtender($this->configurableTypeMock, $this->productRepositoryMock);
    }

    public function testAroundGetIdentities()
    {
        $productIdentity = 'cache_tag_1';
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () use ($productIdentity) {
            return [$productIdentity];
        };

        $productId = 1;
        $parentProductId = 2;
        $parentProductIdentity = 'cache_tag_2';
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->configurableTypeMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $parentProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($parentProductId)
            ->willReturn($parentProductMock);
        $parentProductMock->expects($this->once())
            ->method('getIdentities')
            ->willReturn([$parentProductIdentity]);

        $productIdentities = $this->plugin->aroundGetIdentities($productMock, $proceed);
        $this->assertEquals([$productIdentity, $parentProductIdentity], $productIdentities);
    }
}
