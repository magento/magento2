<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifier;
use Magento\Catalog\Model\Product\ScopedTierPriceManagement;
use Magento\Catalog\Model\Product\TierPriceManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopedTierPriceManagementTest extends TestCase
{
    private const SKU = 'simple';

    /**
     * @var ProductRepositoryInterface&MockObject
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface&MockObject
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface&MockObject
     */
    private $config;

    /**
     * @var PriceModifier&MockObject
     */
    private $priceModifier;

    /**
     * @var Product&MockObject
     */
    private $product;

    /**
     * @var ScopedTierPriceManagement
     */
    private $model;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepository->method('get')->with(self::SKU, ['edit_mode' => true])->willReturn($this->product);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(ScopeConfigInterface::class);
        $this->priceModifier = $this->createMock(PriceModifier::class);

        $this->model = new ScopedTierPriceManagement(
            $this->productRepository,
            $this->storeManager,
            $this->config,
            $this->priceModifier,
            $this->createMock(TierPriceManagement::class)
        );
    }

    public function testRemoveUsesWebsiteOfTierPriceWhenContextWebsiteDiffers(): void
    {
        $this->config->method('getValue')->willReturn(1);
        $website = $this->createMock(Website::class);
        $website->method('getId')->willReturn(0);
        $this->storeManager->method('getWebsite')->willReturn($website);

        $this->priceModifier->expects($this->once())
            ->method('removeTierPrice')
            ->with($this->product, 1, 5.0, 2);

        $this->assertTrue($this->model->remove(self::SKU, $this->createTierPrice(1, 5.0, 2)));
    }

    public function testRemoveFallsBackToContextWebsiteWithoutExtensionAttributes(): void
    {
        $this->config->method('getValue')->willReturn(1);
        $website = $this->createMock(Website::class);
        $website->method('getId')->willReturn(3);
        $this->storeManager->method('getWebsite')->willReturn($website);

        $this->priceModifier->expects($this->once())
            ->method('removeTierPrice')
            ->with($this->product, 1, 5.0, 3);

        $this->assertTrue($this->model->remove(self::SKU, $this->createTierPrice(1, 5.0, null, false)));
    }

    private function createTierPrice(int $groupId, float $qty, ?int $websiteId, bool $withExtension = true)
    {
        $tierPrice = $this->createMock(ProductTierPriceInterface::class);
        $tierPrice->method('getCustomerGroupId')->willReturn($groupId);
        $tierPrice->method('getQty')->willReturn($qty);
        $tierPrice->method('getExtensionAttributes')->willReturn(
            $withExtension
                ? new class ($websiteId) {
                    public function __construct(private readonly ?int $websiteId)
                    {
                    }

                    public function getWebsiteId(): ?int
                    {
                        return $this->websiteId;
                    }
                }
                : null
        );

        return $tierPrice;
    }
}
