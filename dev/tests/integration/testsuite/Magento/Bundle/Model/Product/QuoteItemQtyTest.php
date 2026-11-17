<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCartFixture;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Quantity of a bundle product added to the cart.
 */
class QuoteItemQtyTest extends TestCase
{
    /**
     * A fractional bundle quantity is rejected instead of being truncated to an integer.
     *
     * The selection quantity is 2, so the child quantity (1.5 x 2 = 3) passes the decimal check of the child.
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], 'p1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'qty' => 2], 'link1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testFractionalBundleQtyIsRejected(): void
    {
        $cartId = (int)$this->fixture('cart')->getId();
        $exception = null;
        try {
            $this->addBundleToCart($cartId, 1.5);
        } catch (LocalizedException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'A fractional bundle quantity must not be added to the cart.');
        $this->assertStringContainsString(
            'You cannot use decimal quantity for this product.',
            $exception->getMessage()
        );
        $this->assertCount(0, $this->getCartItemRepository()->getList($cartId));
    }

    /**
     * Updating the bundle quantity to a fractional value is rejected and the quantity stays as it was.
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], 'p1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'qty' => 2], 'link1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testUpdateToFractionalBundleQtyIsRejected(): void
    {
        $cartId = (int)$this->fixture('cart')->getId();
        $this->addBundleToCart($cartId, 2);
        $bundleItem = $this->getBundleItem($cartId);

        $cartItem = clone $bundleItem;
        $cartItem->setItemId($bundleItem->getItemId())->setQty(1.5);
        $exception = null;
        try {
            $this->getCartItemRepository()->save($cartItem);
        } catch (LocalizedException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'A fractional bundle quantity must not be saved to the cart.');
        $this->assertStringContainsString(
            'You cannot use decimal quantity for this product.',
            $exception->getMessage()
        );
        $this->assertEquals(2, $this->getBundleItem($cartId)->getQty());
    }

    /**
     * An integer bundle quantity is still added as requested.
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], 'p1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'qty' => 2], 'link1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testIntegerBundleQtyIsKept(): void
    {
        $cartId = (int)$this->fixture('cart')->getId();
        $this->addBundleToCart($cartId, 2);

        $bundleItem = $this->getBundleItem($cartId);
        $this->assertEquals(2, $bundleItem->getQty());
        $children = $bundleItem->getChildren();
        $this->assertCount(1, $children);
        $this->assertEquals(2, reset($children)->getQty());
    }

    /**
     * Get the bundle item of the cart.
     *
     * @param int $cartId
     * @return object
     */
    private function getBundleItem(int $cartId): object
    {
        $items = $this->getCartItemRepository()->getList($cartId);
        $this->assertCount(1, $items);
        $item = reset($items);
        $this->assertSame('bundle', $item->getProductType());

        return $item;
    }

    /**
     * Add the bundle with its only selection to the cart.
     *
     * @param int $cartId
     * @param float $qty
     * @return void
     */
    private function addBundleToCart(int $cartId, float $qty): void
    {
        Bootstrap::getObjectManager()->get(AddBundleProductToCartFixture::class)->apply(
            [
                'cart_id' => $cartId,
                'product_id' => $this->fixture('bundle')->getId(),
                'qty' => $qty,
                'selections' => [[$this->fixture('p1')->getId()]],
            ]
        );
    }

    /**
     * Get an object created by a data fixture of the test.
     *
     * @param string $name
     * @return object
     */
    private function fixture(string $name): object
    {
        return DataFixtureStorageManager::getStorage()->get($name);
    }

    /**
     * Get the cart item repository.
     *
     * @return CartItemRepositoryInterface
     */
    private function getCartItemRepository(): CartItemRepositoryInterface
    {
        return Bootstrap::getObjectManager()->get(CartItemRepositoryInterface::class);
    }
}
