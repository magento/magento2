<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * Test for empty cart creation mutation
 */
class CreateEmptyCartTest extends GraphQlAbstract
{
    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
    }

    public function testCreateEmptyCart()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testCreateEmptyCartWithNotDefaultStore()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $headerMap = ['Store' => 'fixture_second_store'];

        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertSame('fixture_second_store', $guestCart->getStore()->getCode());
    }
}
