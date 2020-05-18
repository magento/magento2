<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model;

use Magento\Bundle\Model\Product\OptionList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use PHPUnit\Framework\TestCase;

/**
 * Tests for wish list model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 */
class WishlistTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var WishlistFactory */
    private $wishlistFactory;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /** @var SerializerInterface */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->wishlistFactory = $this->objectManager->get(WishlistFactory::class);
        $this->getWishlistByCustomerId = $this->objectManager->get(GetWishlistByCustomerId::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->dataObjectFactory = $this->objectManager->get(DataObjectFactory::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testAddNewItem(): void
    {
        $productSku = 'simple';
        $customerId = 1;
        $product = $this->productRepository->get($productSku);
        $wishlist = $this->getWishlistByCustomerId->execute($customerId);
        $wishlist->addNewItem(
            $product,
            '{"qty":2}'
        );
        $wishlist->addNewItem(
            $product,
            ['qty' => 3]
        );
        $wishlist->addNewItem(
            $product,
            $this->dataObjectFactory->create(['data' => ['qty' => 4]])
        );
        $wishlist->addNewItem($product);
        $wishlistItem = $this->getWishlistByCustomerId->getItemBySku(1, $productSku);
        $this->assertInstanceOf(Item::class, $wishlistItem);
        $this->assertEquals($wishlistItem->getQty(), 10);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testAddNewItemInvalidWishlistItemConfiguration(): void
    {
        $productSku = 'simple';
        $customerId = 1;
        $product = $this->productRepository->get($productSku);
        $wishlist = $this->getWishlistByCustomerId->execute($customerId);
        $this->expectExceptionObject(new \InvalidArgumentException('Invalid wishlist item configuration.'));
        $wishlist->addNewItem($product, '{"qty":2');
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testGetItemCollection(): void
    {
        $productSku = 'simple';
        $item = $this->getWishlistByCustomerId->getItemBySku(1, $productSku);
        $this->assertNotNull($item);
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testGetItemCollectionWithDisabledProduct(): void
    {
        $productSku = 'simple';
        $customerId = 1;
        $product = $this->productRepository->get($productSku);
        $product->setStatus(ProductStatus::STATUS_DISABLED);
        $this->productRepository->save($product);
        $this->assertEmpty($this->getWishlistByCustomerId->execute($customerId)->getItemCollection()->getItems());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddConfigurableProductToWishList(): void
    {
        $configurableProduct = $this->productRepository->get('Configurable product');
        $configurableOptions = $configurableProduct->getTypeInstance()->getConfigurableOptions($configurableProduct);
        $attributeId = key($configurableOptions);
        $option = reset($configurableOptions[$attributeId]);
        $buyRequest = ['super_attribute' => [$attributeId => $option['value_index']]];
        $wishlist = $this->getWishlistByCustomerId->execute(1);
        $wishlist->addNewItem($configurableProduct, $buyRequest);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'Configurable product');
        $this->assertNotNull($item);
        $this->assertWishListItem($item, $option['sku'], $buyRequest);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_without_discounts.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddBundleProductToWishList(): void
    {
        $bundleProduct = $this->productRepository->get('fixed_bundle_product_without_discounts');
        $bundleOptionList = $this->objectManager->create(OptionList::class);
        $bundleOptions = $bundleOptionList->getItems($bundleProduct);
        $option = reset($bundleOptions);
        $productLinks = $option->getProductLinks();
        $this->assertNotNull($productLinks[0]);
        $buyRequest = ['bundle_option' => [$option->getOptionId() => $productLinks[0]->getId()]];
        $skuWithChosenOption = implode('-', [$bundleProduct->getSku(), $productLinks[0]->getSku()]);
        $wishlist = $this->getWishlistByCustomerId->execute(1);
        $wishlist->addNewItem($bundleProduct, $buyRequest);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'fixed_bundle_product_without_discounts');
        $this->assertNotNull($item);
        $this->assertWishListItem($item, $skuWithChosenOption, $buyRequest);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testAddNotExistingItemToWishList(): void
    {
        $wishlist = $this->getWishlistByCustomerId->execute(1);
        $this->expectExceptionObject(new LocalizedException(__('Cannot specify product.')));
        $wishlist->addNewItem(989);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_out_of_stock_with_multiselect_attribute.php
     *
     * @return void
     */
    public function testAddOutOfStockItemToWishList(): void
    {
        $product = $this->productRepository->get('simple_ms_out_of_stock');
        $wishlist = $this->getWishlistByCustomerId->execute(1);
        $this->expectExceptionObject(new LocalizedException(__('Cannot add product without stock to wishlist.')));
        $wishlist->addNewItem($product);
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testUpdateItemQtyInWishList(): void
    {
        $wishlist = $this->getWishlistByCustomerId->execute(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $buyRequest = $this->dataObjectFactory->create(['data' => ['qty' => 55]]);
        $wishlist->updateItem($item->getId(), $buyRequest);
        $updatedItem = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertEquals(55, $updatedItem->getQty());
    }

    /**
     * @return void
     */
    public function testUpdateNotExistingItemInWishList(): void
    {
        $this->expectExceptionObject(new LocalizedException(__('We can\'t specify a wish list item.')));
        $this->wishlistFactory->create()->updateItem(989, []);
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testUpdateNotExistingProductInWishList(): void
    {
        $wishlist = $this->getWishlistByCustomerId->execute(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $item->getProduct()->setId(null);
        $this->expectExceptionObject(new LocalizedException(__('The product does not exist.')));
        $wishlist->updateItem($item, []);
    }

    /**
     * Assert item in wish list.
     *
     * @param Item $item
     * @param string $itemSku
     * @param array $buyRequest
     * @return void
     */
    private function assertWishListItem(Item $item, string $itemSku, array $buyRequest): void
    {
        $this->assertEquals($itemSku, $item->getProduct()->getSku());
        $buyRequestOption = $item->getOptionByCode('info_buyRequest');
        $this->assertEquals($buyRequest, $this->json->unserialize($buyRequestOption->getValue()));
    }
}
