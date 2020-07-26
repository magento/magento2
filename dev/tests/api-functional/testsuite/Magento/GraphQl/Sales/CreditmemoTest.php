<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Bundle\Model\Selection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditMemoCollection;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for credit memo functionality
 */
class CreditmemoTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $customerAuthenticationHeader;

    /** @var CreditmemoFactory */
    private $creditMemoFactory;

    /** @var Order */
    private $order;

    /** @var OrderCollection */
    private $orderCollection;

    /** @var CreditmemoService */
    private $creditMemoService;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var CreditMemoCollection */
    private $creditMemoCollection;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(
            GetCustomerAuthenticationHeader::class
        );
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->creditMemoFactory = $objectManager->get(CreditmemoFactory::class);
        $this->order = $objectManager->create(Order::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->orderCollection = $objectManager->get(OrderCollection::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->creditMemoService = $objectManager->get(CreditmemoService::class);
        $this->creditMemoCollection = $objectManager->get(CreditMemoCollection::class);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_creditmemo_with_two_items.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreditMemoForLoggedInCustomerQuery(): void
    {
        $response = $this->getCustomerOrderWithCreditMemoQuery();

        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'some_comment'],
                    ['message' => 'some_other_comment']
                ],
                'items' => [
                    [
                        'product_name' => 'Simple Related Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_refunded' => 1
                    ],
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_refunded' => 1
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 20
                    ],
                    'grand_total' => [
                        'value' => 20
                    ],
                    'base_grand_total' => [
                        'value' => 10
                    ],
                    'total_shipping' => [
                        'value' => 0
                    ],
                    'total_tax' => [],
                    'shipping_handling' => [
                        'amount_including_tax' => [
                            'value' => 0
                        ],
                        'amount_excluding_tax' => [
                            'value' => 0
                        ],
                        'total_amount' => [
                            'value' => 0
                        ],
                        'taxes' => [],
                        'discounts' => [],
                    ],
                    'adjustment' => [
                        'value' => 1.23
                    ]
                ]
            ]
        ];

        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);

        $creditMemos = $firstOrderItem['credit_memos'] ?? [];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
    }
    /**
     * Test customer refund details from order for bundle product with a partial refund
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     */
    public function testCreditMemoForBundledProductsWithPartialRefund()
    {
        $qty = 2;
        $bundleSku = 'bundle-product-two-dropdown-options';
        $optionsAndSelectionData = $this->getBundleOptionAndSelectionData($bundleSku);

        $cartId = $this->createEmptyCart();
        $this->addBundleProductQuery($cartId, $qty, $bundleSku, $optionsAndSelectionData);
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $orderNumber = $this->placeOrder($cartId);
        $this->prepareInvoice($orderNumber, 2);
        // Create a credit memo
        $order = $this->order->loadByIncrementId($orderNumber);
        /** @var Order\Item $orderItem */
        $orderItem = current($order->getAllItems());
        $orderItem->setQtyRefunded(1);
        $order->addItem($orderItem);
        $order->save();

        $creditMemo = $this->creditMemoFactory->createByOrder($order, $order->getData());
        $creditMemo->setOrder($order);
        $creditMemo->setState(1);
        $creditMemo->setSubtotal(15);
        $creditMemo->setBaseSubTotal(15);
        $creditMemo->setShippingAmount(10);
        $creditMemo->setBaseGrandTotal(23);
        $creditMemo->setGrandTotal(23);
        $creditMemo->setAdjustment(-2.00);
        $creditMemo->addComment("Test comment for partial refund", false, true);
        $creditMemo->save();

        $this->creditMemoService->refund($creditMemo, true);
        $response = $this->getCustomerOrderWithCreditMemoQuery();
        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'Test comment for partial refund']
                ],
                'items' => [
                    [
                        'product_name' => 'Bundle Product With Two dropdown options',
                        'product_sku' => 'bundle-product-two-dropdown-options-simple1-simple2',
                        'product_sale_price' => [
                            'value' => 15
                        ],
                        'quantity_refunded' => 1
                    ],

                ],
                'total' => [
                    'subtotal' => [
                        'value' => 15
                    ],
                    'grand_total' => [
                        'value' => 23
                    ],
                    'base_grand_total' => [
                        'value' => 23
                    ],
                    'total_shipping' => [
                        'value' => 10
                    ],
                    'total_tax' => [],
                    'shipping_handling' => [
                        'amount_including_tax' => [
                            'value' => 10
                        ],
                        'amount_excluding_tax' => [
                            'value' => 10
                        ],
                        'total_amount' => [
                            'value' => 10
                        ],
                        'taxes' => [],
                        'discounts' => [],
                    ],
                    'adjustment' => [
                        'value' => 2
                    ]
                ]
            ]
        ];
        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);

        $creditMemos = $firstOrderItem['credit_memos'] ?? [];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
        $this->deleteOrder();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderNumber)
            ->create();
        $creditmemoRepository = Bootstrap::getObjectManager()->get(CreditmemoRepositoryInterface::class);
        $creditmemos = $creditmemoRepository->getList($searchCriteria)->getItems();
        foreach ($creditmemos as $creditmemo) {
            $creditmemoRepository->delete($creditmemo);
        }
    }

    /**
     * Test customer order with credit memo details for bundle products with taxes and discounts
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     */
    public function testCreditMemoForBundleProductWithTaxesAndDiscounts()
    {
        $quantity = 2;
        $bundleSku = 'bundle-product-two-dropdown-options';
        $optionsAndSelectionData = $this->getBundleOptionAndSelectionData($bundleSku);

        $cartId = $this->createEmptyCart();
        $this->addBundleProductQuery($cartId, $quantity, $bundleSku, $optionsAndSelectionData);
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $orderNumber = $this->placeOrder($cartId);
        $this->prepareInvoice($orderNumber, 2);

        $order = $this->order->loadByIncrementId($orderNumber);
        /** @var Order\Item $orderItem */
        $orderItem = current($order->getAllItems());
        $orderItem->setQtyRefunded(1);
        $order->addItem($orderItem);
        $order->save();

        $creditMemo = $this->creditMemoFactory->createByOrder($order, $order->getData());
        $creditMemo->setOrder($order);
        $creditMemo->setState(1);
        $creditMemo->setSubtotal(15);
        $creditMemo->setBaseSubTotal(15);
        $creditMemo->setShippingAmount(10);
        $creditMemo->setTaxAmount(1.69);
        $creditMemo->setBaseGrandTotal(24.19);
        $creditMemo->setGrandTotal(24.19);
        $creditMemo->setAdjustment(0.00);
        $creditMemo->setDiscountAmount(-2.5);
        $creditMemo->setDiscountDescription('Discount Label for 10% off');
        $creditMemo->addComment("Test comment for refund with taxes and discount", false, true);
        $creditMemo->save();

        $this->creditMemoService->refund($creditMemo, true);
        //$this->prepareCreditmemoAndRefund($orderNumber);
        $response = $this->getCustomerOrderWithCreditMemoQuery();
        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'Test comment for refund with taxes and discount']
                ],
                'items' => [
                    [
                        'product_name' => 'Bundle Product With Two dropdown options',
                        'product_sku' => 'bundle-product-two-dropdown-options-simple1-simple2',
                        'product_sale_price' => [
                            'value' => 15
                        ],
                        'quantity_refunded' => 1
                    ],

                ],
                'total' => [
                    'subtotal' => [
                        'value' => 15
                    ],
                    'grand_total' => [
                        'value' => 24.19
                    ],
                    'base_grand_total' => [
                        'value' => 24.19
                    ],
                    'total_shipping' => [
                        'value' => 10
                    ],
                    'total_tax' => [
                        'value'=> 1.69
                    ],
                    'shipping_handling' => [
                        'amount_including_tax' => [
                            'value' => 10.75
                        ],
                        'amount_excluding_tax' => [
                            'value' => 10
                        ],
                        'total_amount' => [
                            'value' => 10
                        ],
                        'taxes'=> [
                            0 => [
                                'amount'=>['value' => 1.69],
                                'title' => 'US-TEST-*-Rate-1',
                                'rate' => 7.5
                            ]
                        ],
                        'discounts' => [
                            0 => ['amount'=>['value'=> 2.5],
                                'label' => 'Discount Label for 10% off'
                            ]
                        ],
                    ],
                    'adjustment' => [
                        'value' => 0
                    ]
                ]
            ]
        ];
        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);

        $creditMemos = $firstOrderItem['credit_memos'] ?? [];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
        $this->deleteOrder();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderNumber)
            ->create();
        $creditmemoRepository = Bootstrap::getObjectManager()->get(CreditmemoRepositoryInterface::class);
        $creditmemos = $creditmemoRepository->getList($searchCriteria)->getItems();
        foreach ($creditmemos as $creditmemo) {
            $creditmemoRepository->delete($creditmemo);
        }
    }

    /**
     * @return string
     */
    private function createEmptyCart(): string
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        return $response['createEmptyCart'];
    }
    /**
     *  Add bundle product to cart with Graphql query
     *
     * @param string $cartId
     * @param float $qty
     * @param string $sku
     * @param array $optionsAndSelectionData
     * @throws AuthenticationException
     */
    public function addBundleProductQuery(
        string $cartId,
        float $qty,
        string $sku,
        array $optionsAndSelectionData
    ) {
        $query = <<<QUERY
mutation {
  addBundleProductsToCart(input:{
    cart_id:"{$cartId}"
    cart_items:[
      {
        data:{
          sku:"{$sku}"
          quantity:$qty
        }
        bundle_options:[
          {
            id:$optionsAndSelectionData[0]
            quantity:1
            value:["{$optionsAndSelectionData[1]}"]
          }
          {
            id:$optionsAndSelectionData[2]
            quantity:2
            value:["{$optionsAndSelectionData[3]}"]
          }
        ]
      }
    ]
  }) {
    cart {
      items {quantity product {sku}}
      }
    }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $this->assertArrayHasKey('cart', $response['addBundleProductsToCart']);
    }
    /**
     * @param string $cartId
     * @param array $auth
     * @return array
     */
    private function setBillingAddress(string $cartId): void
    {
        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$cartId}"
      billing_address: {
         address: {
          firstname: "John"
          lastname: "Smith"
          company: "Test company"
          street: ["test street 1", "test street 2"]
          city: "Texas City"
          postcode: "78717"
          telephone: "5123456677"
          region: "TX"
          country_code: "US"
         }
      }
    }
  ) {
    cart {
      billing_address {
        __typename
      }
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
    }

    /**
     * @param string $cartId
     * @return array
     */
    private function setShippingAddress(string $cartId): array
    {
        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$cartId"
      shipping_addresses: [
        {
          address: {
            firstname: "test shipFirst"
            lastname: "test shipLast"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "Montgomery"
            region: "AL"
            postcode: "36013"
            country_code: "US"
            telephone: "3347665522"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        available_shipping_methods {
          carrier_code
          method_code
          amount {value}
        }
      }
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $shippingAddress = current($response['setShippingAddressesOnCart']['cart']['shipping_addresses']);
        $availableShippingMethod = current($shippingAddress['available_shipping_methods']);
        return $availableShippingMethod;
    }
    /**
     * @param string $cartId
     * @param array $method
     * @return array
     */
    private function setShippingMethod(string $cartId, array $method): array
    {
        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input:  {
    cart_id: "{$cartId}",
    shipping_methods: [
      {
         carrier_code: "{$method['carrier_code']}"
         method_code: "{$method['method_code']}"
      }
    ]
  }) {
    cart {
      available_payment_methods {
        code
        title
      }
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        $availablePaymentMethod = current($response['setShippingMethodsOnCart']['cart']['available_payment_methods']);
        return $availablePaymentMethod;
    }

    /**
     * @param string $cartId
     * @param array $method
     * @return void
     */
    private function setPaymentMethod(string $cartId, array $method): void
    {
        $query = <<<QUERY
mutation {
  setPaymentMethodOnCart(
    input: {
      cart_id: "{$cartId}"
      payment_method: {
        code: "{$method['code']}"
      }
    }
  ) {
    cart {selected_payment_method {code}}
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
    }

    /**
     * @param string $cartId
     * @return string
     */
    private function placeOrder(string $cartId): string
    {
        $query = <<<QUERY
mutation {
  placeOrder(
    input: {
      cart_id: "{$cartId}"
    }
  ) {
    order {
      order_number
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        return $response['placeOrder']['order']['order_number'];
    }
    /**
     * @param string $bundleSku
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBundleOptionAndSelectionData($bundleSku): array
    {
        /** @var Product $bundleProduct */
        $bundleProduct = $this->productRepository->get($bundleSku);
        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $bundleProduct->getTypeInstance();
        $optionsAndSelections = [];
        /** @var $option \Magento\Bundle\Model\Option */
        $option1 = $typeInstance->getOptionsCollection($bundleProduct)->getFirstItem();
        $option2 = $typeInstance->getOptionsCollection($bundleProduct)->getLastItem();
        $optionId1 =(int) $option1->getId();
        $optionId2 =(int) $option2->getId();
        /** @var Selection $selection */
        $selection1 = $typeInstance->getSelectionsCollection([$option1->getId()], $bundleProduct)->getFirstItem();
        $selectionId1 = (int)$selection1->getSelectionId();
        $selection2 = $typeInstance->getSelectionsCollection([$option2->getId()], $bundleProduct)->getLastItem();
        $selectionId2 = (int)$selection2->getSelectionId();
        array_push($optionsAndSelections, $optionId1, $selectionId1, $optionId2, $selectionId2);
        return $optionsAndSelections;
    }

    /**
     * Prepare invoice for the order
     *
     * @param string $orderNumber
     * @param int|null $qty
     */
    private function prepareInvoice(string $orderNumber, int $qty = null)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($orderNumber);
        $orderItem = current($order->getItems());
        $orderService = Bootstrap::getObjectManager()->create(
            \Magento\Sales\Api\InvoiceManagementInterface::class
        );
        $invoice = $orderService->prepareInvoice($order, [$orderItem->getId() => $qty]);
        $invoice->register();
        $order = $invoice->getOrder();
        $order->setIsInProcess(true);
        $transactionSave = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\DB\Transaction::class);
        $transactionSave->addObject($invoice)->addObject($order)->save();
    }

    /**
     * @return void
     */
    private function deleteOrder(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $order \Magento\Sales\Model\Order */
        $orderCollection = Bootstrap::getObjectManager()->create(OrderCollection::class);
        $creditmemoRepository = Bootstrap::getObjectManager()->get(CreditmemoRepositoryInterface::class);
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     *  Get CustomerOrder with credit memo details
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerOrderWithCreditMemoQuery(): array
    {
        $query =
            <<<QUERY
query {
  customer {
    orders {
        items {
            credit_memos {
                comments {
                    message
                }
                items {
                    product_name
                    product_sku
                    product_sale_price {
                        value
                    }
                    quantity_refunded
                }
                total {
                    subtotal {
                        value
                    }
                    base_grand_total  {
                        value
                    }
                    grand_total {
                        value
                    }
                    total_shipping {
                        value
                    }
                    total_tax {
                        value
                    }
                    shipping_handling {
                         amount_including_tax{value}
                         amount_excluding_tax{value}
                         total_amount{value}
                         taxes {amount{value} title rate}
                         discounts {amount{value} label}
                    }
                    adjustment {
                        value
                    }
                }
            }
        }
    }
  }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        return $response;
    }
}
