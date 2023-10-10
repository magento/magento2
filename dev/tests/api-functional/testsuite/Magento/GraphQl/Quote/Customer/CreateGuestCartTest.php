<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for guest cart creation mutation for customer
 */
class CreateGuestCartTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testFailForLoggedInUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Use `Query.cart` or `Query.customerCart` for logged in customer.");

        $query = $this->getQuery();
        $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
mutation {
  createGuestCart {
    cart {
      id
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMapWithCustomerToken(
        string $username = 'customer@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    protected function tearDown(): void
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        foreach ($quoteCollection as $quote) {
            $this->quoteResource->delete($quote);

            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quote->getId())
                ->delete();
        }
        parent::tearDown();
    }
}
