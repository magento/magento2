<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\GraphQl\Sales\Fixtures\CustomerPlaceOrderWithDownloadable;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditmemoCollection;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Sales\Model\Order\Creditmemo\ItemFactory;
use Magento\Framework\Registry;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\InvoiceManagementInterface;

/**
 * Tests downloadable product fields in Orders, Invoices, CreditMemo and Shipments
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RetrieveOrdersWithDownloadableProductTest extends GraphQlAbstract
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var GetCustomerAuthenticationHeader */
    private $customerAuthenticationHeader;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CreditmemoService */
    private $creditMemoService;

    /** @var Order */
    private $order;

    /** @var CreditmemoFactory */
    private $creditMemoFactory;

    /** @var ItemFactory  */
    private $creditmemoItemFactory;

    /** @var CustomerPlaceOrderWithDownloadable  */
    private $customerPlaceOrderWithDownloadable;

    /** @var InvoiceManagementInterface  */
    private $invoiceManagement;

    /** @var OrderCollection  */
    private $orderCollection;

    /** @var CreditmemoRepositoryInterface  */
    private $creditmemoRepository;

    /** @var CreditmemoCollection  */
    private $creditmemoCollection;

    /** @var Registry  */
    private $registry;

    /** @var Transaction  */
    private $transaction;

    protected function setUp():void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->order = $objectManager->create(Order::class);
        $this->creditMemoService = $objectManager->get(CreditmemoService::class);
        $this->creditMemoFactory = $objectManager->get(CreditmemoFactory::class);
        $this->creditmemoItemFactory = $objectManager->create(ItemFactory::class);
        $this->customerPlaceOrderWithDownloadable = $objectManager->create(CustomerPlaceOrderWithDownloadable::class);
        $this->invoiceManagement = $objectManager->create(InvoiceManagementInterface::class);
        $this->orderCollection = $objectManager->create(OrderCollection::class);
        $this->creditmemoRepository = $objectManager->get(CreditmemoRepositoryInterface::class);
        $this->creditmemoCollection = $objectManager->create(CreditmemoCollection::class);
        $this->registry = $objectManager->get(Registry::class);
        $this->transaction = $objectManager->create(Transaction::class);
    }

    protected function tearDown(): void
    {
        $this->cleanUpCreditMemos();
        $this->deleteOrder();
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/order_with_customer_and_downloadable_product.php
     * @magentoApiDataFixture Magento/Downloadable/_files/customer_order_with_invoice_downloadable_product.php
     */
    public function testGetCustomerOrdersDownloadableProduct()
    {
        $orderNumber = '100000001';
        $customerOrders = $this->getCustomersOrderQuery($orderNumber);
        $customerOrderItemsInResponse = $customerOrders[0]['items'];

        $this->assertNotEmpty($customerOrderItemsInResponse);
        $downloadableItemInTheOrder = $customerOrderItemsInResponse[0];
        $this->assertEquals(
            'downloadable-product',
            $downloadableItemInTheOrder['product_sku']
        );
        $priceOfDownloadableItemInOrder = $downloadableItemInTheOrder['product_sale_price']['value'];
        $this->assertEquals(10, $priceOfDownloadableItemInOrder);
        $this->assertArrayHasKey('downloadable_links', $downloadableItemInTheOrder);
        $downloadableLinksFromResponse = $downloadableItemInTheOrder['downloadable_links'];
        $this->assertNotEmpty($downloadableLinksFromResponse);

        $downloadableProduct = $this->productRepository->get('downloadable-product');
        /** @var LinkInterface $downloadableProductLinks */
        $downloadableProductLinks = $downloadableProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $linkId = $downloadableProductLinks[0]->getId();
        $expectedDownloadableLinksData =
            [
                [
                    'title' =>'Downloadable Product Link',
                    'sort_order' => 1,
                    'uid'=> base64_encode("downloadable/{$linkId}")
                ]
            ];
        $this->assertResponseFields($expectedDownloadableLinksData, $downloadableLinksFromResponse);
        // invoices assertions
        $customerOrderItemsInvoicesResponse  = $customerOrders[0]['invoices'][0];
        $this->assertNotEmpty($customerOrderItemsInvoicesResponse);
        $this->assertNotEmpty($customerOrderItemsInvoicesResponse['number']);
        $customerOrderItemsInvoicesItemsResponse = $customerOrderItemsInvoicesResponse['items'][0];
        $this->assertEquals('Downloadable Product', $customerOrderItemsInvoicesItemsResponse['product_name']);
        $this->assertEquals(10, $customerOrderItemsInvoicesItemsResponse['product_sale_price']['value']);
        $this->assertEquals(1, $customerOrderItemsInvoicesItemsResponse['quantity_invoiced']);
        $downloadableItemLinks = $customerOrderItemsInvoicesItemsResponse['downloadable_links'];
        $this->assertNotEmpty($downloadableItemLinks);

        $downloadableProduct = $this->productRepository->get('downloadable-product');
        /** @var LinkInterface $downloadableProductLinks */
        $downloadableProductLinks = $downloadableProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $linkId = $downloadableProductLinks[0]->getId();
        $expectedDownloadableLinksData =
            [
                [
                    'title' =>'Downloadable Product Link',
                    'sort_order' => 1,
                    'uid'=> base64_encode("downloadable/{$linkId}")
                ]
            ];
        $this->assertResponseFields($expectedDownloadableLinksData, $downloadableItemLinks);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_purchased_separately_links.php
     */
    public function testGetCustomerOrdersAndCreditMemoDownloadable()
    {
        //Place order with downloadable product
        $qty = 1;
        $downloadableSku = 'downloadable-product-with-purchased-separately-links';
        $orderResponse = $this->customerPlaceOrderWithDownloadable->placeOrderWithDownloadableProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => $downloadableSku, 'quantity' => $qty]
        );
        $orderNumber = $orderResponse['placeOrder']['order']['order_number'];
        //End place order with downloadable product

        // prepare invoice
        $this->prepareInvoice($orderNumber, 1);
        $order = $this->order->loadByIncrementId($orderNumber);
        // Create a credit memo
        $creditMemo = $this->creditMemoFactory->createByOrder($order, $order->getData());
        $creditMemo->setOrder($order);
        $creditMemo->setState(1);
        $creditMemo->setSubtotal(12);
        $creditMemo->setBaseSubTotal(12);
        $creditMemo->setBaseGrandTotal(12);
        $creditMemo->setGrandTotal(12);
        $creditMemo->setAdjustment(-2.00);
        $creditMemo->addComment("Test comment for downloadable refund", false, true);
        $creditMemo->save();
        $this->creditMemoService->refund($creditMemo, true);
        $response = $this->getCustomerOrderWithCreditMemoQuery();
        $downloadableProduct = $this->productRepository->get('downloadable-product-with-purchased-separately-links');
        /** @var LinkInterface $downloadableProductLinks */
        $downloadableProductLinks = $downloadableProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $linkId = $downloadableProductLinks[0]->getId();
        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'Test comment for downloadable refund']
                ],
                'items' => [
                    [
                        'product_name'=> 'Downloadable Product (Links can be purchased separately)',
                        'product_sku' => 'downloadable-product-with-purchased-separately-links',
                        'product_sale_price' => ['value' => 12],
                        'discounts' => [],
                        'quantity_refunded' => 1,
                        'downloadable_links' => [
                            [
                                'uid'=> base64_encode("downloadable/{$linkId}"),
                                'title' => 'Downloadable Product Link 1']
                        ]
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 12
                    ],
                    'grand_total' => [
                        'value' => 12,
                        'currency' => 'USD'
                    ],
                    'base_grand_total' => [
                        'value' => 12,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 0
                    ],
                    'total_tax' => [
                        'value' => 0
                    ],
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
                        'taxes' => []

                    ],
                    'adjustment' => [
                        'value' => 2
                    ]
                ]
            ]
        ];
        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);
        $this->assertArrayHasKey('credit_memos', $firstOrderItem);

        $creditMemos = $firstOrderItem['credit_memos'];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
    }

    /**
     * Prepare invoice for the order
     *
     * @param string $orderNumber
     * @param int|null $qty
     */
    private function prepareInvoice(string $orderNumber, ?int $qty = null)
    {
        /** @var Order $order */
        $order = $this->order->loadByIncrementId($orderNumber);
        $orderItem = current($order->getItems());
        $invoice = $this->invoiceManagement->prepareInvoice($order, [$orderItem->getId() => $qty]);
        $invoice->register();
        $order = $invoice->getOrder();
        $order->setIsInProcess(true);
        $this->transaction->addObject($invoice)->addObject($order)->save();
    }

    /**
     * Get customer order query with invoices
     *
     * @param string $orderNumber
     * @return array
     */
    private function getCustomersOrderQuery($orderNumber): array
    {
        $query =
            <<<QUERY
{
     customer {
       orders(filter:{number:{eq:"{$orderNumber}"}}) {
         total_count
         items {
           id
           number
           order_date
           status
           items{
            __typename
            product_sku
            product_name
            product_url_key
            product_sale_price{value}
            quantity_ordered
            discounts{amount{value} label}
            ... on DownloadableOrderItem{
              downloadable_links{
                title
                sort_order
                uid
              }
              entered_options{value label}
              product_sku
              product_name
              quantity_ordered
          }
         }
         total {
             base_grand_total{value currency}
             grand_total{value currency}
             subtotal {value currency }
             total_tax{value currency}
             taxes {amount{value currency} title rate}
             total_shipping{value currency}
             shipping_handling
             {
               amount_including_tax{value}
               amount_excluding_tax{value}
               total_amount{value}
               discounts{amount{value}}
               taxes {amount{value} title rate}
             }
             discounts {amount{value currency} label}
           }
        invoices {
              number
              items {
              	product_name
                product_sale_price{value currency}
                quantity_invoiced
                ... on DownloadableInvoiceItem {
                   downloadable_links
                  {
                   sort_order
                   title
                   uid
                  }
                  id
                  product_name
                  product_sale_price{value}
                  quantity_invoiced
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

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'];
        return $customerOrderItemsInResponse;
    }

    /**
     *  Get CustomerOrder with credit memo details
     *
     * @return array
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
                comments { message}
                items {
                    product_name
                    product_sku
                    product_sale_price {value }
                    discounts { amount{value currency} label }
                    quantity_refunded
                     ... on DownloadableCreditMemoItem
                  {
                    product_name
                    discounts{amount{value}}
                    downloadable_links{
                      uid
                      title
                    }
                    quantity_refunded
                  }
                }
                total {
                    subtotal {
                        value
                    }
                    base_grand_total  {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
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

    /**
     * @return void
     */
    private function deleteOrder(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($this->orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * @return void
     */
    private function cleanUpCreditMemos(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        foreach ($this->creditmemoCollection as $creditmemo) {
            $this->creditmemoRepository->delete($creditmemo);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }
}
