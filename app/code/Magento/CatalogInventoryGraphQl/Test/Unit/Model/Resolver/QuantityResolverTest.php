<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Test\Unit\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Config\Source\NotAvailableMessage;
use Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\CartItem\ProductStock;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuantityResolverTest extends TestCase
{
    /**
     * Scope config path for not_available_message
     */
    private const CONFIG_PATH_NOT_AVAILABLE_MESSAGE = 'cataloginventory/options/not_available_message';

    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var Field&Stub
     */
    private Field $fieldStub;

    /**
     * @var ContextInterface&Stub
     */
    private ContextInterface $contextStub;

    /**
     * @var ResolveInfo&Stub
     */
    private ResolveInfo $resolveInfoStub;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->fieldStub = $this->createStub(Field::class);
        $this->contextStub = $this->createStub(ContextInterface::class);
        $this->resolveInfoStub = $this->createStub(ResolveInfo::class);
    }

    /**
     * Regression test for ACP2E-4840 / ACQE-9928: the resolver must return null when the product
     * is unavailable and the store is configured to show "Not enough items for sale".
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveReturnsNullWhenProductUnavailableAndConfigIsNotEnoughItems(): void
    {
        $productStub = $this->createStub(Product::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $productStockMock = $this->createMock(ProductStock::class);
        $resolver = $this->createResolver($productStockMock, $scopeConfigMock);

        $productStub->method('getTypeId')->willReturn('simple');
        $productStockMock->expects($this->once())->method('checkIfProductIsAvailable')
            ->with($productStub)
            ->willReturn(false);
        $scopeConfigMock->expects($this->once())->method('getValue')
            ->with(self::CONFIG_PATH_NOT_AVAILABLE_MESSAGE, ScopeInterface::SCOPE_STORE)
            ->willReturn(NotAvailableMessage::VALUE_NOT_ENOUGH_ITEMS);
        $productStockMock->expects($this->never())->method('getSaleableQty');

        $this->assertNull(
            $resolver->resolve(
                $this->fieldStub,
                $this->contextStub,
                $this->resolveInfoStub,
                ['model' => $productStub]
            )
        );
    }

    /**
     * An unavailable product must still return its actual (zero) saleable quantity, not null,
     * when the store is configured to show "Only X of Y available" instead.
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveReturnsSaleableQtyWhenUnavailableButConfigIsOnlyXOfY(): void
    {
        $saleableQty = 0.0;
        $productStub = $this->createStub(Product::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $productStockMock = $this->createMock(ProductStock::class);
        $resolver = $this->createResolver($productStockMock, $scopeConfigMock);

        $productStub->method('getTypeId')->willReturn('simple');
        $productStockMock->expects($this->once())->method('checkIfProductIsAvailable')
            ->with($productStub)
            ->willReturn(false);
        $scopeConfigMock->expects($this->once())->method('getValue')
            ->with(self::CONFIG_PATH_NOT_AVAILABLE_MESSAGE, ScopeInterface::SCOPE_STORE)
            ->willReturn(NotAvailableMessage::VALUE_ONLY_X_OF_Y);
        $productStockMock->expects($this->once())->method('getSaleableQty')
            ->with($productStub, null)
            ->willReturn($saleableQty);

        $this->assertSame(
            $saleableQty,
            $resolver->resolve(
                $this->fieldStub,
                $this->contextStub,
                $this->resolveInfoStub,
                ['model' => $productStub]
            )
        );
    }

    /**
     * An available product must always return its actual saleable quantity, even when the store
     * is configured to show "Not enough items for sale" — the config check must short-circuit
     * and never run when the product is available. This was the root cause of ACP2E-4840: an
     * incorrectly-scoped stock lookup made available custom-stock products look unavailable.
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveReturnsSaleableQtyWhenProductAvailableRegardlessOfConfig(): void
    {
        $saleableQty = 13.0;
        $productStub = $this->createStub(Product::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $productStockMock = $this->createMock(ProductStock::class);
        $resolver = $this->createResolver($productStockMock, $scopeConfigMock);

        $productStub->method('getTypeId')->willReturn('simple');
        $productStockMock->expects($this->once())->method('checkIfProductIsAvailable')
            ->willReturn(true);
        $scopeConfigMock->expects($this->never())->method('getValue');
        $productStockMock->expects($this->once())->method('getSaleableQty')
            ->with($productStub, null)
            ->willReturn($saleableQty);

        $this->assertSame(
            $saleableQty,
            $resolver->resolve(
                $this->fieldStub,
                $this->contextStub,
                $this->resolveInfoStub,
                ['model' => $productStub]
            )
        );
    }

    /**
     * Configurable products must be re-fetched by SKU before checking availability, so the
     * variant-level stock (not the configurable parent) is evaluated.
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveResolvesConfigurableProductBeforeCheckingAvailability(): void
    {
        $sku = 'configurable_sku';
        $productStub = $this->createStub(Product::class);
        $variantProductStub = $this->createStub(Product::class);
        $productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $productStockMock = $this->createMock(ProductStock::class);
        $saleableQty = 5.0;
        $resolver = $this->createResolver($productStockMock, null, $productRepositoryMock);

        $productStub->method('getTypeId')->willReturn('configurable');
        $productStub->method('getSku')->willReturn($sku);
        $productRepositoryMock->expects($this->once())->method('get')
            ->with($sku)
            ->willReturn($variantProductStub);
        $productStockMock->expects($this->once())->method('checkIfProductIsAvailable')
            ->with($variantProductStub)
            ->willReturn(true);
        $productStockMock->expects($this->once())->method('getSaleableQty')
            ->with($variantProductStub, null)
            ->willReturn($saleableQty);

        $this->assertSame(
            $saleableQty,
            $resolver->resolve(
                $this->fieldStub,
                $this->contextStub,
                $this->resolveInfoStub,
                ['model' => $productStub]
            )
        );
    }

    /**
     * Configurable products must return null when the re-fetched variant is unavailable and the store
     * is configured to show "Not enough items for sale".
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveReturnsNullWhenConfigurableUnavailableAndConfigIsNotEnoughItems(): void
    {
        $sku = 'configurable_sku';
        $productStub = $this->createStub(Product::class);
        $variantProductStub = $this->createStub(Product::class);
        $productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $productStockMock = $this->createMock(ProductStock::class);
        $resolver = $this->createResolver($productStockMock, $scopeConfigMock, $productRepositoryMock);

        $productStub->method('getTypeId')->willReturn('configurable');
        $productStub->method('getSku')->willReturn($sku);
        $productRepositoryMock->expects($this->once())->method('get')
            ->with($sku)
            ->willReturn($variantProductStub);
        $productStockMock->expects($this->once())->method('checkIfProductIsAvailable')
            ->with($variantProductStub)
            ->willReturn(false);
        $scopeConfigMock->expects($this->once())->method('getValue')
            ->with(self::CONFIG_PATH_NOT_AVAILABLE_MESSAGE, ScopeInterface::SCOPE_STORE)
            ->willReturn(NotAvailableMessage::VALUE_NOT_ENOUGH_ITEMS);
        $productStockMock->expects($this->never())->method('getSaleableQty');

        $this->assertNull(
            $resolver->resolve(
                $this->fieldStub,
                $this->contextStub,
                $this->resolveInfoStub,
                ['model' => $productStub]
            )
        );
    }

    /**
     * When resolving quantity for a cart item, the resolver must delegate directly to
     * getSaleableQtyByCartItem and skip the product-availability/config check entirely.
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveWithCartItemReturnsSaleableQtyByCartItem(): void
    {
        $cartItemStub = $this->createStub(Item::class);
        $productStockMock = $this->createMock(ProductStock::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $saleableQty = 7.0;
        $resolver = $this->createResolver($productStockMock, $scopeConfigMock);

        $productStockMock->expects($this->once())->method('getSaleableQtyByCartItem')
            ->with($cartItemStub, null)
            ->willReturn($saleableQty);
        $productStockMock->expects($this->never())->method('checkIfProductIsAvailable');
        $scopeConfigMock->expects($this->never())->method('getValue');

        $this->assertSame(
            $saleableQty,
            $resolver->resolve(
                $this->fieldStub,
                $this->contextStub,
                $this->resolveInfoStub,
                ['cart_item' => $cartItemStub]
            )
        );
    }

    /**
     * Resolver must throw when neither a cart item nor a product model is provided in $value.
     *
     * @covers \Magento\CatalogInventoryGraphQl\Model\Resolver\QuantityResolver::resolve
     * @return void
     */
    public function testResolveThrowsExceptionWhenModelIsNotSpecified(): void
    {
        $productStockStub = $this->createStub(ProductStock::class);
        $resolver = $this->createResolver($productStockStub);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');

        $resolver->resolve(
            $this->fieldStub,
            $this->contextStub,
            $this->resolveInfoStub,
            []
        );
    }

    /**
     * Create resolver instance with provided dependencies.
     *
     * @param ProductStock|MockObject $productStock
     * @param ScopeConfigInterface|MockObject|null $scopeConfig
     * @param ProductRepositoryInterface|MockObject|null $productRepository
     * @return QuantityResolver
     */
    private function createResolver(
        ProductStock $productStock,
        ?ScopeConfigInterface $scopeConfig = null,
        ?ProductRepositoryInterface $productRepository = null
    ): QuantityResolver {
        return $this->objectManager->getObject(
            QuantityResolver::class,
            [
                'productRepositoryInterface' => $productRepository
                    ?? $this->createStub(ProductRepositoryInterface::class),
                'scopeConfig' => $scopeConfig ?? $this->createStub(ScopeConfigInterface::class),
                'productStock' => $productStock,
            ]
        );
    }
}
