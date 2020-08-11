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
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class RetrieveOrdersTest for DownloadableProduct
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

    protected function setUp():void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->order = $objectManager->create(Order::class);
        $this->creditMemoService = $objectManager->get(CreditmemoService::class);
        $this->creditMemoFactory = $objectManager->get(CreditmemoFactory::class);
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
        $response = $this->getCustomerOrderQuery($orderNumber);
        $customerOrderItemsInResponse = $response[0]['items'];

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
        $customerOrderItemsInvoicesResponse  = $response[0]['invoices'][0];
        $this->assertNotEmpty($customerOrderItemsInvoicesResponse);
        $this->assertNotEmpty($customerOrderItemsInvoicesResponse['number']);
        $customerOrderItemsInvoicesItemsResponse = $customerOrderItemsInvoicesResponse['items'][0];
        $this->assertEquals('Downloadable Product', $customerOrderItemsInvoicesItemsResponse['product_name']);
        $this->assertEquals(10, $customerOrderItemsInvoicesItemsResponse['product_sale_price']['value']);
        $this->assertEquals(1, $customerOrderItemsInvoicesItemsResponse['quantity_invoiced']);
        $downloadableItemInTheInvoice = $customerOrderItemsInvoicesItemsResponse['downloadable_links'];
        $this->assertNotEmpty($downloadableItemInTheInvoice);

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
        $this->assertResponseFields($expectedDownloadableLinksData, $downloadableItemInTheInvoice);
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
        /** @var CustomerPlaceOrderWithDownloadable $downloadableProductOrderFixture */
        $downloadableProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrderWithDownloadable::class);
        $orderResponse = $downloadableProductOrderFixture->placeOrderWithDownloadableProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => $downloadableSku, 'quantity' => $qty]
        );
        $orderNumber = $orderResponse['placeOrder']['order']['order_number'];
        //End place order with downloadable product

        // prepare invoice
        $this->prepareInvoice($orderNumber, 1);
        $order = $this->order->loadByIncrementId($orderNumber);
        /** @var Order\Item $orderItem */
        $orderItem = current($order->getAllItems());
        $orderItem->setQtyRefunded(1);
        $order->addItem($orderItem);
        $order->save();
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
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $creditMemoItems */
  //      $creditMemoItems = $creditMemo->getItemByOrderId($order->getId());
//        $creditMemoItems->setCreditmemo($creditMemo);
//        $creditMemoItems->setOrderItemId($orderItem->getId());
 //       $creditMemoItems->setQty(1);
 //       $creditMemoItems->save();

        $this->creditMemoService->refund($creditMemo, true);
        $response = $this->getCustomerOrderWithCreditMemoQuery();
        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'Test comment for downloadable refund']
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
     * Get customer order query
     *
     * @param string $orderNumber
     * @return array
     */
    private function getCustomerOrderQuery($orderNumber): array
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
              entered_options{value id}
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
                comments {
                    message
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
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $order \Magento\Sales\Model\Order */
        $orderCollection = Bootstrap::getObjectManager()->create(OrderCollection::class);
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * @return void
     */
    private function cleanUpCreditMemos(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $creditmemoRepository = Bootstrap::getObjectManager()->get(CreditmemoRepositoryInterface::class);
        $creditmemoCollection = Bootstrap::getObjectManager()->create(Collection::class);
        foreach ($creditmemoCollection as $creditmemo) {
            $creditmemoRepository->delete($creditmemo);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
