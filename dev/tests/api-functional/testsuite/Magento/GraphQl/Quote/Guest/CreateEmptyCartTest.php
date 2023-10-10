<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
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

    /**
     * @var string
     */
    private $maskedQuoteId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    public function testDeprecatedCreateEmptyCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertEmpty($response['createEmptyCart']);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
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
