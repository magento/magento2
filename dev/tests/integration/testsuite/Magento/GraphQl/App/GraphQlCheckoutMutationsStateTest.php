<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQl\App\State\GraphQlStateDiff;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea graphql
 */
class GraphQlCheckoutMutationsStateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GraphQlStateDiff|null
     */
    private ?GraphQlStateDiff $graphQlStateDiff = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        if (!class_exists(GraphQlStateDiff::class)) {
            $this->markTestSkipped('GraphQlStateDiff class is not available on this version of Magento.');
        }

        $this->graphQlStateDiff = new GraphQlStateDiff();
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->graphQlStateDiff->tearDown();
        $this->graphQlStateDiff = null;
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testCreateEmptyCart() : void
    {
        $this->graphQlStateDiff->testState(
            $this->getEmptyCart(),
            [],
            [],
            [],
            'createEmptyCart',
            '"data":{"createEmptyCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @return void
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
    ]
    public function testAddSimpleProductToCart(): void
    {
        $fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $cartId1 = $fixtures->get('cart1')->getId();
        $cartId2 = $fixtures->get('cart2')->getId();
        $query = $this->getAddProductToCartQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId1, 'qty' => 1, 'sku' => 'simple_product'],
            ['cartId' => $cartId2, 'qty' => 1, 'sku' => 'simple_product'],
            [],
            'addSimpleProductsToCart',
            '"data":{"addSimpleProductsToCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_discount.php
     * @return void
     */
    public function testAddCouponToCart()
    {
        $cartId = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getAddCouponToCartQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId, 'couponCode' => '2?ds5!2d'],
            ['cartId' => $cartId, 'couponCode' => 'CART_FIXED_DISCOUNT_15'],
            [],
            'applyCouponToCart',
            '"data":{"applyCouponToCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/virtual_product.php
     * @return void
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
    ]
    public function testAddVirtualProductToCart()
    {
        $fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $cartId1 = $fixtures->get('cart1')->getId();
        $cartId2 = $fixtures->get('cart2')->getId();
        $query = $this->getAddVirtualProductToCartQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId1, 'quantity' => 1, 'sku' => 'virtual_product'],
            ['cartId' => $cartId2, 'quantity' => 1, 'sku' => 'virtual_product'],
            [],
            'addVirtualProductsToCart',
            '"data":{"addVirtualProductsToCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @return void
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
    ]
    public function testAddBundleProductToCart()
    {
        $fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $cartId1 = $fixtures->get('cart1')->getId();
        $cartId2 = $fixtures->get('cart2')->getId();
        $query = $this->getAddBundleProductToCartQuery('bundle-product');
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId1],
            ['cartId' => $cartId2],
            [],
            'addBundleProductsToCart',
            '"data":{"addBundleProductsToCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @return void
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
    ]
    public function testAddConfigurableProductToCart(): void
    {
        $fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $cartId1 = $fixtures->get('cart1')->getId();
        $cartId2 = $fixtures->get('cart2')->getId();
        $query = $this->getAddConfigurableProductToCartQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId1, 'quantity' => 2, 'parentSku' => 'configurable', 'childSku' => 'simple_20'],
            ['cartId' => $cartId2, 'quantity' => 2, 'parentSku' => 'configurable', 'childSku' => 'simple_20'],
            [],
            'addConfigurableProductsToCart',
            '"data":{"addConfigurableProductsToCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_purchased_separately_links.php
     * @return void
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
    ]
    public function testAddDownloadableProductToCart(): void
    {
        $fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $cartId1 = $fixtures->get('cart1')->getId();
        $cartId2 = $fixtures->get('cart2')->getId();
        $sku = 'downloadable-product-with-purchased-separately-links';
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);
        $query = $this->getAddDownloadableProductToCartQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId1, 'qty' => 1, 'sku' => $sku, 'linkId' => $linkId],
            ['cartId' => $cartId2, 'qty' => 1, 'sku' => $sku, 'linkId' => $linkId],
            [],
            'addDownloadableProductsToCart',
            '"data":{"addDownloadableProductsToCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @return void
     */
    public function testSetShippingAddressOnCart(): void
    {
        $cartId = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getShippingAddressQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId],
            [],
            [],
            'setShippingAddressesOnCart',
            '"data":{"setShippingAddressesOnCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @return void
     */
    public function testSetBillingAddressOnCart(): void
    {
        $cartId = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getBillingAddressQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId],
            [],
            [],
            'setBillingAddressOnCart',
            '"data":{"setBillingAddressOnCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @return void
     */
    public function testSetShippingMethodsOnCart(): void
    {
        $cartId = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getShippingMethodsQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId, 'shippingMethod' => 'flatrate'],
            [],
            [],
            'setShippingMethodsOnCart',
            '"data":{"setShippingMethodsOnCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testSetPaymentMethodOnCart(): void
    {
        $cartId = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getPaymentMethodQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId],
            [],
            [],
            'setPaymentMethodOnCart',
            '"data":{"setPaymentMethodOnCart":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     */
    public function testPlaceOrder(): void
    {
        $cartId = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getPlaceOrderQuery();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId' => $cartId],
            [],
            [],
            'placeOrder',
            '"data":{"placeOrder":',
            $this
        );
    }

    private function getBillingAddressQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!) {
              setBillingAddressOnCart(
                input: {
                  cart_id: $cartId
                  billing_address: {
                    address: {
                      firstname: "John"
                      lastname: "Doe"
                      street: ["123 Main Street"]
                      city: "New York"
                      region: "NY"
                      postcode: "10001"
                      country_code: "US"
                      telephone: "555-555-5555"
                    }
                  }
                }
              ) {
                cart {
                  id
                  billing_address {
                    firstname
                    lastname
                    street
                    city
                    region {
                      code
                      label
                    }
                    postcode
                    country {
                      code
                      label
                    }
                    telephone
                  }
                }
              }
            }
            QUERY;
    }

    private function getShippingAddressQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!) {
              setShippingAddressesOnCart(
                input: {
                  cart_id: $cartId
                  shipping_addresses: [
                    {
                      address: {
                        firstname: "John"
                        lastname: "Doe"
                        street: ["123 Main Street"]
                        city: "New York"
                        region: "NY"
                        postcode: "10001"
                        country_code: "US"
                        telephone: "555-555-5555"
                      }
                    }
                  ]
                }
              ) {
                cart {
                  id
                  shipping_addresses {
                    firstname
                    lastname
                    street
                    city
                    region {
                      code
                      label
                    }
                    postcode
                    country {
                      code
                      label
                    }
                    telephone
                    available_shipping_methods {
                      carrier_code
                      method_code
                      amount {
                        value
                      }
                    }
                  }
                }
              }
            }
            QUERY;
    }
    /**
     * Function returns array of all product's links
     *
     * @param string $sku
     * @return array
     */
    private function getProductsLinks(string $sku) : array
    {
        $result = [];
        $productRepository = $this->graphQlStateDiff->getTestObjectManager()
            ->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku, false, null, true);

        foreach ($product->getDownloadableLinks() as $linkObject) {
            $result[$linkObject->getLinkId()] = [
                'title' => $linkObject->getTitle(),
                'link_type' => null, //deprecated field
                'price' => $linkObject->getPrice(),
            ];
        }
        return $result;
    }

    private function getAddDownloadableProductToCartQuery(): string
    {
        return <<<'MUTATION'
                    mutation($cartId: String!, $qty: Float!, $sku: String!, $linkId: Int!) {
                        addDownloadableProductsToCart(
                            input: {
                                cart_id: $cartId,
                                cart_items: [
                                    {
                                        data: {
                                            quantity: $qty,
                                            sku: $sku
                                        },
                                        downloadable_product_links: [
                                            {
                                                link_id: $linkId
                                            }
                                        ]
                                    }
                                ]
                            }
                        ) {
                            cart {
                                items {
                                    quantity
                                    ... on DownloadableCartItem {
                                        links {
                                            title
                                            link_type
                                            price
                                        }
                                        samples {
                                            id
                                            title
                                        }
                                    }
                                }
                            }
                        }
                    }
                MUTATION;
    }

    private function getAddConfigurableProductToCartQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!, $quantity: Float!, $parentSku: String!, $childSku: String!) {
              addConfigurableProductsToCart(
                input:{
                  cart_id: $cartId
                  cart_items:{
                    parent_sku: $parentSku
                    data:{
                      sku: $childSku
                      quantity:$quantity
                    }
                  }
                }
              ) {
                cart {
                  id
                  items {
                    id
                    quantity
                    product {
                      sku
                    }
                    ... on ConfigurableCartItem {
                      configurable_options {
                        id
                        option_label
                        value_id
                        value_label
                      }
                    }
                  }
                }
              }
            }
            QUERY;
    }

    private function getAddBundleProductToCartQuery(string $sku)
    {
        $productRepository = $this->graphQlStateDiff->getTestObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);
        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var $option \Magento\Bundle\Model\Option */
        $option = $typeInstance->getOptionsCollection($product)->getFirstItem();
        /** @var \Magento\Catalog\Model\Product $selection */
        $selection = $typeInstance->getSelectionsCollection([$option->getId()], $product)->getFirstItem();
        $optionId = $option->getId();
        $selectionId = $selection->getSelectionId();

        return <<<QUERY
            mutation(\$cartId: String!) {
              addBundleProductsToCart(input:{
                cart_id:\$cartId
                cart_items:[
                  {
                    data:{
                      sku:"{$sku}"
                      quantity:1
                    }
                    bundle_options:[
                      {
                        id:{$optionId}
                        quantity:1
                        value:[
                          "{$selectionId}"
                        ]
                      }
                    ]
                  }
                ]
              }) {
                cart {
                  items {
                    id
                    uid
                    quantity
                    product {
                      sku
                    }
                    ... on BundleCartItem {
                      bundle_options {
                        id
                        uid
                        label
                        type
                        values {
                          id
                          uid
                          label
                          price
                          quantity
                        }
                      }
                    }
                  }
                }
              }
            }
            QUERY;
    }

    /**
     * @return string
     */
    private function getAddProductToCartQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!, $qty: Float!, $sku: String!) {
              addSimpleProductsToCart(
                input: {
                  cart_id: $cartId
                  cart_items: [
                    {
                      data: {
                        quantity: $qty
                        sku: $sku
                      }
                    }
                  ]
                }
              ) {
                cart {
                  items {
                    quantity
                    product {
                      sku
                    }
                  }
                }
              }
            }
        QUERY;
    }

    /**
     * @return string
     */
    private function getAddVirtualProductToCartQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!, $quantity: Float!, $sku: String!) {
              addVirtualProductsToCart(
                input: {
                  cart_id: $cartId
                  cart_items: [
                    {
                      data: {
                        quantity: $quantity
                        sku: $sku
                      }
                    }
                  ]
                }
              ) {
                cart {
                  id
                  items {
                    quantity
                    product {
                      sku
                    }
                  }
                }
              }
            }
            QUERY;
    }

    /**
     * @return string
     */
    private function getEmptyCart(): string
    {
        return <<<QUERY
                 mutation {
                    createEmptyCart
                 }
            QUERY;
    }

    /**
     * @return string
     */
    private function getShippingMethodsQuery()
    {
        return <<<'QUERY'
            mutation($cartId: String!, $shippingMethod: String!) {
              setShippingMethodsOnCart(
                input: {
                  cart_id: $cartId
                  shipping_methods: [
                    {
                      carrier_code: $shippingMethod
                      method_code: $shippingMethod
                    }
                  ]
                }
              ) {
                cart {
                  id
                  shipping_addresses {
                    selected_shipping_method {
                      carrier_code
                      method_code
                      carrier_title
                      method_title
                    }
                  }
                }
              }
            }
            QUERY;
    }

    /**
     * @return string
     */
    private function getPaymentMethodQuery()
    {
        return <<<'QUERY'
            mutation($cartId: String!) {
              setPaymentMethodOnCart(
                input: {
                  cart_id: $cartId
                  payment_method: {
                    code: "checkmo"
                  }
                }
              ) {
                cart {
                  id
                  selected_payment_method {
                    code
                    title
                  }
                }
              }
            }
            QUERY;
    }

    /**
     * @return string
     */
    private function getPlaceOrderQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!) {
              placeOrder(
                input: {
                  cart_id: $cartId
                }
              ) {
                order {
                  order_number
                }
              }
            }
            QUERY;
    }

    /**
     * @return string
     */
    private function getAddCouponToCartQuery(): string
    {
        return <<<'QUERY'
            mutation($cartId: String!, $couponCode: String!) {
              applyCouponToCart(
                input: {
                  cart_id: $cartId
                  coupon_code: $couponCode
                }
              ) {
                cart {
                  id
                  applied_coupons {
                    code
                  }
                }
              }
            }
            QUERY;
    }
}
