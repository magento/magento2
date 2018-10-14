<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrdersTest
 */
class OrdersTest extends GraphQlAbstract
{
	/**
	 * @var CustomerTokenServiceInterface
	 */
	private $customerTokenService;

	/**
	 * {@inheritdoc}
	 */
    protected function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
	    $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

	/**
	 * @magentoApiDataFixture Magento/Customer/_files/customer.php
	 * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
	 */
    public function testOrdersQuery()
    {
	    $query =
		    <<<QUERY
query {
	customerOrders {
		items {
			id
			increment_id
			created_at
		    grant_total
		    state
		    status
		}
	}
}
QUERY;

	    $currentEmail = 'customer@example.com';
	    $currentPassword = 'password';

	    $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

	    $expectedData = [
		    [
			    'increment_id' => '100000002',
			    'state' => \Magento\Sales\Model\Order::STATE_NEW,
			    'status' => 'processing',
			    'grant_total' => 120.00
		    ],
		    [
			    'increment_id' => '100000003',
			    'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
			    'status' => 'processing',
			    'grant_total' => 130.00
		    ],
		    [
			    'increment_id' => '100000004',
			    'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
			    'status' => 'closed',
			    'grant_total' => 140.00
		    ],
		    [
			    'increment_id' => '100000005',
			    'state' => \Magento\Sales\Model\Order::STATE_COMPLETE,
			    'status' => 'complete',
			    'grant_total' => 150.00
		    ],
		    [
			    'increment_id' => '100000006',
			    'state' => \Magento\Sales\Model\Order::STATE_COMPLETE,
			    'status' => 'complete',
			    'grant_total' => 160.00
		    ]
	    ];

	    $actualData = $response['customerOrders']['items'];

	    foreach ($expectedData as $key => $data) {
		    $this->assertEquals($data['increment_id'], $actualData[$key]['increment_id']);
		    $this->assertEquals($data['grant_total'], $actualData[$key]['grant_total']);
		    $this->assertEquals($data['state'], $actualData[$key]['state']);
		    $this->assertEquals($data['status'], $actualData[$key]['status']);
	    }
    }

	/**
	 * @param string $email
	 * @param string $password
	 * @return array
	 * @throws \Magento\Framework\Exception\AuthenticationException
	 */
	private function getCustomerAuthHeaders(string $email, string $password): array
	{
		$customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
		return ['Authorization' => 'Bearer ' . $customerToken];
	}
}
