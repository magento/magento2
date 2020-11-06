<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Plugin\ProductIdentitiesExtender;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\ConfigurableProduct\Model\Plugin\ProductIdentitiesExtender class.
 */
class ProductIdentitiesExtenderTest extends TestCase
{
    /**
     * @var MockObject|Configurable
     */
    private $configurableTypeMock;

    /**
     * @var MockObject|ProductRepositoryInterface
     */
    private $productRepositoryMock;

    /**
     * @var ProductIdentitiesExtender
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configurableTypeMock = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMock();

        $this->plugin = new ProductIdentitiesExtender($this->configurableTypeMock, $this->productRepositoryMock);
    }

    /**
     * Verify after get identities
     *
     * @return void
     */
    public function testAfterGetIdentities()
    {
        $productId = 1;
        $productIdentity = 'cache_tag_1';
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentProductId = 2;
        $parentProductIdentity = 'cache_tag_2';
        $parentProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->exactly(2))
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);
        $this->configurableTypeMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $this->productRepositoryMock->expects($this->exactly(2))
            ->method('getById')
            ->with($parentProductId)
            ->willReturn($parentProductMock);
        $parentProductMock->expects($this->exactly(2))
            ->method('getIdentities')
            ->willReturn([$parentProductIdentity]);

        $productIdentities = $this->plugin->afterGetIdentities($productMock, [$productIdentity]);
        $this->assertEquals([$productIdentity, $parentProductIdentity], $productIdentities);

        $this->configurableTypeMock->expects($this->never())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $productIdentities = $this->plugin->afterGetIdentities($productMock, [$productIdentity]);
        $this->assertEquals([$productIdentity, $parentProductIdentity], $productIdentities);
    }
}
