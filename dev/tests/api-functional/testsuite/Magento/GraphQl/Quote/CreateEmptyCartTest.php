<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Quote\Api\Data\CartInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * Test for empty cart creation mutation
 */
class CreateEmptyCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdMask
     */
    private $quoteIdMask;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quoteIdMask = $this->objectManager->create(QuoteIdMask::class);
        $this->guestCartRepository = $this->objectManager->create(GuestCartRepositoryInterface::class);
    }

    public function testCreateEmptyCartForGuest()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        /** @var CartInterface $guestCart */
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartForRegisteredCustomer()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;

        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        /* guestCartRepository is used for registered customer to get the cart hash */
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
    }
}
