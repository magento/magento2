<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Saving a cart must not replace items whose configuration has not changed.
 *
 * @see \Magento\Quote\Model\Quote\Item\CartItemPersister
 */
class CartItemPersisterKeepsItemTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    #[
        DataFixture(
            ProductFixture::class,
            [
                'price' => 55,
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'title' => 'Engraving',
                        'price' => 0,
                        'price_type' => 'fixed',
                        'sku' => 'engraving',
                    ],
                ],
            ],
            as: 'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testSaveKeepsItemWithExtraOptionAndCustomPrice(): void
    {
        $storage = DataFixtureStorageManager::getStorage();
        $cartId = (int)$storage->get('cart')->getId();
        $product = $this->objectManager->get(ProductRepositoryInterface::class)
            ->get($storage->get('product')->getSku());
        $optionId = $product->getOptions()[0]->getOptionId();

        $this->objectManager->get(AddProductToCartFixture::class)->apply([
            'cart_id' => $cartId,
            'product_id' => $product->getId(),
            'buy_request' => ['qty' => 1, 'options' => [$optionId => 'text']],
        ]);
        $initialRepository = $this->objectManager->create(QuoteRepository::class);
        $quote = $initialRepository->get($cartId);
        $quoteItem = current($quote->getAllItems());
        $quoteItem->setCustomPrice(8)->setOriginalCustomPrice(8);
        $quoteItem->addOption([
            'product' => $quoteItem->getProduct(),
            'code' => 'some_extension_option',
            'value' => '1',
            'product_id' => $quoteItem->getProductId(),
        ]);
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $initialRepository->save($quote);
        $itemId = (int)$quoteItem->getId();

        $repository = $this->objectManager->create(QuoteRepository::class);
        $loadedQuote = $repository->getActive($cartId);
        $this->objectManager->create(Repository::class, ['quoteRepository' => $repository])->getList($cartId);
        $this->assertNotEmpty($loadedQuote->getItems());
        $repository->save($loadedQuote);

        $items = $this->objectManager->create(QuoteRepository::class)->getActive($cartId)->getAllItems();
        $this->assertCount(1, $items);
        $savedItem = current($items);
        $this->assertEquals($itemId, $savedItem->getItemId());
        $this->assertEquals(8, $savedItem->getCustomPrice());
        $this->assertNotNull($savedItem->getOptionByCode('some_extension_option'));
    }
}
