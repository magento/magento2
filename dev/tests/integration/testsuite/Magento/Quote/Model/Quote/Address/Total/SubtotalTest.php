<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Address\Total;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('frontend')]
#[DbIsolation(true)]
class SubtotalTest extends TestCase
{
    #[DataFixture(ProductFixture::class, ['sku' => 'zz-a'], as: 'productA')]
    #[DataFixture(ProductFixture::class, ['sku' => 'zz-b'], as: 'productB')]
    public function testCollectRemovesOnlyInvalidUnsavedItem(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $quote = Bootstrap::getObjectManager()->create(Quote::class)->setStoreId(1);
        $itemA = $quote->addProduct($fixtures->get('productA'), 1);
        $itemB = $quote->addProduct($fixtures->get('productB'), 1);

        self::assertInstanceOf(Item::class, $itemA);
        self::assertInstanceOf(Item::class, $itemB);
        self::assertNull($itemA->getId());
        self::assertNull($itemB->getId());
        $itemB->getProduct()->setStatus(Status::STATUS_DISABLED);
        $this->assertOnlyInvalidItemIsDeleted($quote, $itemA, $itemB);
    }

    #[DataFixture(ProductFixture::class, ['sku' => 'zz-a'], as: 'productA')]
    #[DataFixture(ProductFixture::class, ['sku' => 'zz-b'], as: 'productB')]
    #[DataFixture(GuestCartFixture::class, as: 'cart')]
    #[DataFixture(
        AddProductToCartFixture::class,
        ['cart_id' => '$cart.id$', 'product_id' => '$productA.id$'],
        as: 'itemA'
    )]
    #[DataFixture(
        AddProductToCartFixture::class,
        ['cart_id' => '$cart.id$', 'product_id' => '$productB.id$'],
        as: 'itemB'
    )]
    public function testCollectRemovesOnlyInvalidSavedItem(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $objectManager = Bootstrap::getObjectManager();
        $quote = $objectManager->create(Quote::class);
        $objectManager->get(QuoteResource::class)->load($quote, $fixtures->get('cart')->getId());
        $itemA = $quote->getItemById($fixtures->get('itemA')->getId());
        $itemB = $quote->getItemById($fixtures->get('itemB')->getId());

        self::assertInstanceOf(Item::class, $itemA);
        self::assertInstanceOf(Item::class, $itemB);
        self::assertNotNull($itemA->getId());
        self::assertNotNull($itemB->getId());

        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->getById($itemB->getProductId(), false, 0, true);
        $product->setStatus(Status::STATUS_DISABLED);
        $productRepository->save($product);
        $itemB->setProduct($productRepository->getById($itemB->getProductId(), false, $quote->getStoreId(), true));

        self::assertFalse((bool)$itemA->isDeleted());
        self::assertFalse((bool)$itemB->isDeleted());
        self::assertFalse($itemB->getProduct()->isVisibleInCatalog());
        $this->assertOnlyInvalidItemIsDeleted($quote, $itemA, $itemB);
    }

    /**
     * @param Quote $quote
     * @param Item $itemA
     * @param Item $itemB
     * @return void
     */
    private function assertOnlyInvalidItemIsDeleted(Quote $quote, Item $itemA, Item $itemB): void
    {
        $quote->getBillingAddress();
        $quote->getShippingAddress();
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        self::assertFalse((bool)$itemA->isDeleted(), 'The enabled item must remain in the quote.');
        self::assertTrue((bool)$itemB->isDeleted(), 'The disabled item must be deleted.');
    }
}
