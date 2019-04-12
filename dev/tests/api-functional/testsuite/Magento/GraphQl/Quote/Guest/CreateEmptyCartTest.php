<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
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
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var string
     */
    private $maskedQuoteId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->maskedQuoteIdToQuoteId = $objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    public function testCreateEmptyCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertEquals('default', $guestCart->getStore()->getCode());
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testCreateEmptyCartWithNotDefaultStore()
    {
        $query = $this->getQuery();
        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlMutation($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertSame('fixture_second_store', $guestCart->getStore()->getCode());
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

    public function tearDown()
    {
        if (null !== $this->maskedQuoteId) {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($this->maskedQuoteId);

            $quote = $this->quoteFactory->create();
            $this->quoteResource->load($quote, $quoteId);
            $this->quoteResource->delete($quote);

            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quoteId)
                ->delete();
        }
        parent::tearDown();
    }
}
