<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CustomerDownloadableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CustomerDownloadableProductTest extends GraphQlAbstract
{

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoApiDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     */
    public function testGetCustomerDownloadableProducts()
    {
        $query = <<<MUTATION
mutation {
  generateCustomerToken(
    email: "customer@example.com"
    password: "password"
  ) {
    token
  }
}
MUTATION;
        $response = $this->graphQlMutation($query);
        $token = $response['generateCustomerToken']['token'];
        $this->headers = ['Authorization' => 'Bearer ' . $token];

        $query = <<<QUERY
        {
    customerDownloadableProducts{
        items{
            order_increment_id
            date
            status
            download_url
            remaining_downloads
        }
    }

}
QUERY;
        $objectManager = ObjectManager::getInstance();

        $searchCriteria = $objectManager->get(SearchCriteriaBuilder::class)->create();

        $orderRepository = $objectManager->create(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)->getItems();
        $order = array_pop($orders);

        $builder = $objectManager->create(\Magento\Framework\Api\FilterBuilder::class);
        $filter = $builder
            ->setField('email')
            ->setValue('customer@example.com');

        $searchCriteria = $objectManager->get(
            SearchCriteriaBuilder::class
        )->addFilter(
            'email',
            'customer@example.com'
        )->create();

        $customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $customers = $customerRepository->getList($searchCriteria)
            ->getItems();
        $customer = array_pop($customers);

        $order->setCustomerId($customer->getId())->setCustomerIsGuest(false)->save();

        $response = $this->graphQlQuery($query, [], '', $this->headers);

        $expectedResponse = [
            'customerDownloadableProducts' => [
                'items' => [
                    [
                        'order_increment_id' => $order->getIncrementId(),
                        'date' => '',
                        'status' => 'pending',
                        'download_url' => 'http://example.com/downloadable.txt',
                        'remaining_downloads' => '1'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResponse, $response);
    }
}
