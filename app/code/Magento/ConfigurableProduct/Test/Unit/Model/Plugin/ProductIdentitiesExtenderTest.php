<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\ConfigurableProduct\Model\Plugin\ProductIdentitiesExtender;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\Store;
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
     * @var ProductWebsiteLink|MockObject
     */
    private $productWebsiteLinkMock;

    /**
     * @var ProductIdentitiesExtender
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configurableTypeMock = $this->createMock(Configurable::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productWebsiteLinkMock = $this->createMock(ProductWebsiteLink::class);

        $this->plugin = new ProductIdentitiesExtender(
            $this->configurableTypeMock,
            $this->productRepositoryMock,
            $this->productWebsiteLinkMock
        );
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
        $productMock = $this->createMock(Product::class);
        $parentProductId = 2;
        $parentProductIdentity = 'cache_tag_2';
        $parentProductMock = $this->createMock(Product::class);

        $productMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->exactly(2))
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $storeMock = $this->createMock(Store::class);
        $productMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->configurableTypeMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $this->productWebsiteLinkMock->expects($this->never())
            ->method('getWebsiteIdsByProductId');
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

    public function testAfterGetIdentitiesWhenWebsitesMatched()
    {
        $productId = 1;
        $websiteId = 1;
        $productIdentity = 'cache_tag_1';
        $productMock = $this->createMock(Product::class);
        $parentProductId = 2;
        $parentProductIdentity = 'cache_tag_2';
        $parentProductMock = $this->createMock(Product::class);

        $productMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $storeMock = $this->createMock(Store::class);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->configurableTypeMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->with($parentProductId)
            ->willReturn([$websiteId]);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($parentProductId)
            ->willReturn($parentProductMock);
        $parentProductMock->expects($this->once())
            ->method('getIdentities')
            ->willReturn([$parentProductIdentity]);

        $productIdentities = $this->plugin->afterGetIdentities($productMock, [$productIdentity]);
        $this->assertEquals([$productIdentity, $parentProductIdentity], $productIdentities);
    }

    public function testAfterGetIdentitiesWhenWebsitesNotMatched()
    {
        $productId = 1;
        $productIdentity = 'cache_tag_1';
        $productMock = $this->createMock(Product::class);
        $parentProductId = 2;

        $productMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $storeMock = $this->createMock(Store::class);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->configurableTypeMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->with($parentProductId)
            ->willReturn([2]);
        $this->productRepositoryMock->expects($this->never())
            ->method('getById');

        $productIdentities = $this->plugin->afterGetIdentities($productMock, [$productIdentity]);
        $this->assertEquals([$productIdentity], $productIdentities);
    }
}
