<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Cart;

use Laminas\Stdlib\Parameters;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class add product to cart controller.
 *
 * @see \Magento\Checkout\Controller\Cart\Add
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class AddTest extends AbstractController
{
    /** @var SerializerInterface */
    private $json;

    /** @var CheckoutSessionFactory */
    private $checkoutSessionFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /** @var Escaper */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->checkoutSessionFactory = $this->_objectManager->get(CheckoutSessionFactory::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->executeInStoreContext = $this->_objectManager->get(ExecuteInStoreContext::class);
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->prepareReferer();
    }

    /**
     * Test with simple product and activated redirect to cart
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoConfigFixture current_store checkout/cart/redirect_to_cart 1
     *
     * @return void
     */
    public function testMessageAtAddToCartWithRedirect(): void
    {
        $checkoutSession = $this->checkoutSessionFactory->create();
        $postData = [
            'qty' => '1',
            'product' => '1',
            'custom_price' => 1,
            'isAjax' => 1,
        ];
        $this->dispatchAddToCartRequest($postData);
        $this->assertEquals(
            $this->json->serialize(['backUrl' => 'http://localhost/checkout/cart/']),
            $this->getResponse()->getBody()
        );
        $this->assertSessionMessages(
            $this->contains((string)__('You added %1 to your shopping cart.', 'Simple Product')),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertCount(1, $checkoutSession->getQuote()->getItemsCollection());
    }

    /**
     * Test with simple product and deactivated redirect to cart
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoConfigFixture current_store checkout/cart/redirect_to_cart 0
     *
     * @return void
     */
    public function testMessageAtAddToCartWithoutRedirect(): void
    {
        $checkoutSession = $this->checkoutSessionFactory->create();
        $postData = [
            'qty' => '1',
            'product' => '1',
            'custom_price' => 1,
            'isAjax' => 1,
        ];
        $this->dispatchAddToCartRequest($postData);
        $this->assertFalse($this->getResponse()->isRedirect());
        $this->assertEquals('[]', $this->getResponse()->getBody());
        $this->assertSessionMessages(
            $this->contains(
                (string)__("\n" . 'You added %1 to your ' .
                    '<a href="http://localhost/checkout/cart/">shopping cart</a>.', 'Simple Product')
            ),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertCount(1, $checkoutSession->getQuote()->getItemsCollection());
    }

    /**
     * @dataProvider wrongParamsDataProvider
     *
     * @param array $params
     * @return void
     */
    public function testWithWrongParams(array $params): void
    {
        $this->dispatchAddToCartRequest($params);
        $this->assertRedirect($this->stringContains('http://localhost/test'));
    }

    /**
     * @return array
     */
    public function wrongParamsDataProvider(): array
    {
        return [
            'empty_params' => ['params' => []],
            'with_not_existing_product_id' => ['params' => ['product' => 989]],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     *
     * @return void
     */
    public function testAddProductFromUnavailableWebsite(): void
    {
        $product = $this->productRepository->get('simple-1');
        $postData = ['product' => $product->getId()];
        $this->executeInStoreContext->execute('fixture_second_store', [$this, 'dispatchAddToCartRequest'], $postData);
        $this->assertRedirect($this->stringContains('http://localhost/test'));
        $message = $this->escaper->escapeHtml(
            (string)__('The product wasn\'t found. Verify the product and try again.')
        );
        $this->assertSessionMessages($this->contains($message), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testAddProductWithUnavailableQty(): void
    {
        $product = $this->productRepository->get('simple-1');
        $postData = ['product' => $product->getId(), 'qty' => 1000];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $message = (string)__('The requested qty is not available');
        $this->assertSessionMessages($this->contains($message), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/max_sale_qty 20
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testAddProductWithSetConfigMaxSaleQty(): void
    {
        $product = $this->productRepository->get('simple-1');
        $postData = ['product' => $product->getId(), 'qty' => 21];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $expectedMessages = [
            (string)__('The most you may purchase is %1.', 20),
            (string)__('The requested qty exceeds the maximum qty allowed in shopping cart'),
        ];
        $this->assertErrorMessage($expectedMessages);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/min_sale_qty 5
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testAddProductWithSetConfigMinSaleQty(): void
    {
        $product = $this->productRepository->get('simple-1');
        $postData = ['product' => $product->getId(), 'qty' => 2];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $expectedMessages = [
            (string)__('The fewest you may purchase is %1.', 5),
            (string)__('The fewest you may purchase is %1', 5),
        ];
        $this->assertErrorMessage($expectedMessages);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/enable_qty_increments 1
     * @magentoConfigFixture current_store cataloginventory/item_options/qty_increments 3
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testAddProductWithSetConfigQtyIncrements(): void
    {
        $product = $this->productRepository->get('simple-1');
        $postData = ['product' => $product->getId(), 'qty' => 2];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $message = (string)__('You can buy this product only in quantities of %1 at a time.', 3);
        $this->assertSessionMessages($this->contains($message), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_min_max_sale_qty.php
     *
     * @return void
     */
    public function testAddProductWithMinSaleQty(): void
    {
        $product = $this->productRepository->get('simple_product_min_max_sale_qty');
        $postData = ['product' => $product->getId(), 'qty' => 2];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $expectedMessages = [
            (string)__('The fewest you may purchase is %1.', 5),
            (string)__('The fewest you may purchase is %1', 5),
            ];
        $this->assertErrorMessage($expectedMessages);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_min_max_sale_qty.php
     *
     * @return void
     */
    public function testAddProductWithMaxSaleQty(): void
    {
        $product = $this->productRepository->get('simple_product_min_max_sale_qty');
        $postData = ['product' => $product->getId(), 'qty' => 25];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $expectedMessages = [
            (string)__('The most you may purchase is %1.', 20),
            (string)__('The requested qty exceeds the maximum qty allowed in shopping cart'),
        ];
        $this->assertErrorMessage($expectedMessages);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_with_qty_increments.php
     *
     * @return void
     */
    public function testAddProductWithQtyIncrements(): void
    {
        $product = $this->productRepository->get('simple_product_with_qty_increments');
        $postData = ['product' => $product->getId(), 'qty' => 2];
        $this->dispatchAddToCartRequest($postData);
        $this->assertRedirect($this->stringContains($product->getProductUrl()));
        $message = (string)__('You can buy this product only in quantities of 3 at a time.');
        $this->assertSessionMessages($this->contains($message), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_related_multiple.php
     *
     * @return void
     */
    public function testAddProductWithRelated(): void
    {
        $checkoutSession = $this->checkoutSessionFactory->create();
        $product = $this->productRepository->get('simple_with_cross');
        $params = [
            'product' => $product->getId(),
            'related_product' => implode(',', $product->getRelatedProductIds()),
        ];
        $this->dispatchAddToCartRequest($params);
        $this->assertCount(3, $checkoutSession->getQuote()->getItemsCollection());
        $message = "\n" . 'You added %1 to your <a href="http://localhost/checkout/cart/">shopping cart</a>.';
        $this->assertSessionMessages(
            $this->contains((string)__($message, $product->getName())),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Assert error message.
     *
     * @param array $expectedMessages
     * @return void
     */
    public function assertErrorMessage(array $expectedMessages): void
    {
        $actualMessage = current($this->getMessages(MessageInterface::TYPE_ERROR));
        $this->assertRegExp('/' . implode('|', $expectedMessages) . '/', $actualMessage);
    }

    /**
     * Dispatch add product to cart request.
     *
     * @param array $postData
     * @return void
     */
    public function dispatchAddToCartRequest(array $postData = []): void
    {
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('checkout/cart/add');
    }

    /**
     * Prepare referer to test.
     *
     * @return void
     */
    private function prepareReferer(): void
    {
        $parameters = $this->_objectManager->create(Parameters::class);
        $parameters->set('HTTP_REFERER', 'http://localhost/test');
        $this->getRequest()->setServer($parameters);
    }
}
