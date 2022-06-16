<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Magento\Wishlist\Model\Item;

/**
 * Test for update wish list item.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class UpdateItemOptionsTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Escaper */
    private $escaper;

    /** @var SerializerInterface */
    private $json;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_configurable_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUpdateItemOptions(): void
    {
        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'Configurable product');
        $this->assertNotNull($item);
        $params = [
            'id' => $item->getId(),
            'product' => $item->getProductId(),
            'super_attribute' => $this->performConfigurableOption($item->getProduct()),
            'qty' => 5,
        ];
        $this->performUpdateWishListItemRequest($params);
        $message = sprintf("%s has been updated in your Wish List.", $item->getProduct()->getName());
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $item->getWishlistId()));
        $this->assertUpdatedItem(
            $this->getWishlistByCustomerId->getItemBySku(1, 'Configurable product'),
            $params
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testUpdateItemOptionsWithoutParams(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performUpdateWishListItemRequest([]);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testUpdateNotExistingItem(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performUpdateWishListItemRequest(['product' => 989]);
        $message = $this->escaper->escapeHtml("We can't specify a product.");
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testUpdateOutOfStockItem(): void
    {
        $product = $this->productRepository->get('simple3');
        $this->customerSession->setCustomerId(1);
        $this->performUpdateWishListItemRequest(['product' => $product->getId()]);
        $message = $this->escaper->escapeHtml("We can't specify a product.");
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_out_of_stock_with_multiselect_attribute.php
     *
     * @return void
     */
    public function testUpdateItemNotSpecifyAsWishListItem(): void
    {
        $product = $this->productRepository->get('simple_ms_out_of_stock');
        $this->customerSession->setCustomerId(1);
        $this->performUpdateWishListItemRequest(['product' => $product->getId()]);
        $message = $this->escaper->escapeHtml("We can't specify a wish list item.");
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/'));
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_grouped_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUpdateItemOptionsForGroupedProduct(): void
    {
        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'grouped');
        $this->assertNotNull($item);
        $params = [
            'id' => $item->getId(),
            'product' => $item->getProductId(),
            'super_group' => $this->performGroupedOption(),
            'qty' => 1,
        ];
        $this->performUpdateWishListItemRequest($params);
        $message = sprintf("%s has been updated in your Wish List.", $item->getProduct()->getName());
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $item->getWishlistId()));
        $this->assertUpdatedItem(
            $this->getWishlistByCustomerId->getItemBySku(1, 'grouped'),
            $params
        );
    }

    /**
     * Perform request update wish list item.
     *
     * @param array $params
     * @return void
     */
    private function performUpdateWishListItemRequest(array $params): void
    {
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/updateItemOptions');
    }

    /**
     * Assert updated item in wish list.
     *
     * @param Item $item
     * @param array $expectedData
     * @return void
     */
    private function assertUpdatedItem(Item $item, array $expectedData): void
    {
        $this->assertEquals($expectedData['qty'], $item->getQty());
        $buyRequestOption = $this->json->unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $buyRequestOption[$key]);
        }
    }

    /**
     * Perform configurable option to select.
     *
     * @param ProductInterface $product
     * @return array
     */
    private function performConfigurableOption(ProductInterface $product): array
    {
        $configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
        $attributeId = key($configurableOptions);
        $option = reset($configurableOptions[$attributeId]);

        return [$attributeId => $option['value_index']];
    }

    /**
     * Perform group option to select.
     *
     * @return array
     */
    private function performGroupedOption(): array
    {
        $simple1 = $this->productRepository->get('simple_11');
        $simple2 = $this->productRepository->get('simple_22');

        return [
            $simple1->getId() => '3',
            $simple2->getId() => '3',
        ];
    }
}
