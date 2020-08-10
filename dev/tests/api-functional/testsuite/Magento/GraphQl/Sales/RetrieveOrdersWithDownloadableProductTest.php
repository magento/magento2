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
use Magento\Sales\Api\OrderRepositoryInterface;
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

    protected function setUp():void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
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
     * @magentoApiDataFixture Magento/Downloadable/_files/order_with_customer_and_downloadable_product_with_multiple_links.php
     */
    public function testGetCustomerOrdersDownloadableWithMultipleLinks()
    {
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
}
