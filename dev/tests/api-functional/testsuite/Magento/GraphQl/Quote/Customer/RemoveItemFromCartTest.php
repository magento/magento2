<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 *  Test for removeItemFromCartTest mutation
 */
class RemoveItemFromCartTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testRemoveItemFromCart()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();

        $query = $this->prepareMutationQuery($maskedQuoteId, $itemId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('removeItemFromCart', $response);
        $this->assertArrayHasKey('cart', $response['removeItemFromCart']);
        $this->assertCount(0, $response['removeItemFromCart']['cart']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testRemoveItemFromNonExistentCart()
    {
        $query = $this->prepareMutationQuery('non_existent_masked_id', 1);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testRemoveNonExistentItem()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $notExistentItemId = 999;

        $this->expectExceptionMessage("Cart doesn't contain the {$notExistentItemId} item.");

        $query = $this->prepareMutationQuery($maskedQuoteId, $notExistentItemId);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testRemoveItemIfItemIsNotBelongToCart()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_1', 'reserved_order_id');
        $firstQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $secondQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $secondQuote->setCustomerId(1);
        $this->quoteResource->save($secondQuote);
        $secondQuoteItemId = (int)$secondQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $this->expectExceptionMessage("Cart doesn't contain the {$secondQuoteItemId} item.");

        $query = $this->prepareMutationQuery($firstQuoteMaskedId, $secondQuoteItemId);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testRemoveItemFromGuestCart()
    {
        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());
        $guestQuoteItemId = (int)$guestQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$guestQuoteMaskedId\""
        );

        $query = $this->prepareMutationQuery($guestQuoteMaskedId, $guestQuoteItemId);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testRemoveItemFromAnotherCustomerCart()
    {
        $anotherCustomerQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $anotherCustomerQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $anotherCustomerQuote->setCustomerId(2);
        $this->quoteResource->save($anotherCustomerQuote);

        $anotherCustomerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$anotherCustomerQuote->getId());
        $anotherCustomerQuoteItemId = (int)$anotherCustomerQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$anotherCustomerQuoteMaskedId\""
        );

        $query = $this->prepareMutationQuery($anotherCustomerQuoteMaskedId, $anotherCustomerQuoteItemId);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @param string $input
     * @param string $message
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     */
    public function testUpdateWithMissedItemRequiredParameters(string $input, string $message)
    {
        $query = <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      {$input}
    }
  ) {
    cart {
      items {
        qty
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage($message);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @return array
     */
    public function dataProviderUpdateWithMissedRequiredParameters(): array
    {
        return [
            'missed_cart_id' => [
                'cart_item_id: 1',
                'Required parameter "cart_id" is missing.'
            ],
            'missed_cart_item_id' => [
                'cart_id: "test"',
                'Required parameter "cart_item_id" is missing.'
            ],
        ];
    }

    /**
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function prepareMutationQuery(string $maskedQuoteId, int $itemId): string
    {
        return <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_item_id: {$itemId}
    }
  ) {
    cart {
      items {
        qty
      }
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
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
