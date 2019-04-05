<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * Test for empty cart creation mutation for customer
 */
class CreateEmptyCartTest extends GraphQlAbstract
{
    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCart()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;

        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
    }
}
