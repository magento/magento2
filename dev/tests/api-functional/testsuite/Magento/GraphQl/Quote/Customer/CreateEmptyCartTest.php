<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Framework\Math\Random as RandomDataGenerator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

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

    /**
     * @var RandomDataGenerator
     */
    private $randomDataGenerator;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->maskedQuoteIdToQuoteId = $objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
        $this->randomDataGenerator = $objectManager->get(RandomDataGenerator::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @throws AuthenticationException
     * @throws NoSuchEntityException
     */
    public function testCreateEmptyCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
        self::assertEquals('default', $guestCart->getStore()->getCode());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testCreateEmptyCartWithCartId()
    {
        $uniqueHash = $this->randomDataGenerator->getUniqueHash();

        $query = $this->getQueryWithCartId('cart_id : "' . $uniqueHash . '"');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartWithNotDefaultStore()
    {
        $query = $this->getQuery();

        $headerMap = $this->getHeaderMapWithCustomerToken();
        $headerMap['Store'] = 'fixture_second_store';
        $response = $this->graphQlMutation($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        /* guestCartRepository is used for registered customer to get the cart hash */
        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
        self::assertEquals('fixture_second_store', $guestCart->getStore()->getCode());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @dataProvider dataProviderValidateCreateEmptyCartWithSpecifiedCartId
     * @param string $input
     * @param string $message
     * @throws \Exception
     */
    public function testValidateCreateEmptyCartWithSpecifiedCartId(string $input, string $message)
    {
        $input = str_replace('provide_non_unique_id', $this->addEmptyCartWithCartId(), $input);
        $input = str_replace('provide_hash_with_prefix', $this->randomDataGenerator->getUniqueHash('prefix'), $input);

        $query = $this->getQueryWithCartId($input);

        $this->expectExceptionMessage($message);
        $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());
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

    /**
     * @param string $input
     * @return string
     */
    private function getQueryWithCartId(string $input): string
    {
        return <<<QUERY
mutation {
 createEmptyCart(
   input : {
     {$input}
   }
 )
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMapWithCustomerToken(
        string $username = 'customer@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
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

    /**
     * Return masked id for created empty cart.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @return mixed
     * @throws LocalizedException
     */
    private function addEmptyCartWithCartId()
    {
        $uniqueHash = $this->randomDataGenerator->getUniqueHash();
        $query = $this->getQueryWithCartId('cart_id : "' . $uniqueHash . '"');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());

        return $response['createEmptyCart'];
    }

    /**
     * @return array
     */
    public function dataProviderValidateCreateEmptyCartWithSpecifiedCartId(): array
    {
        return [
            'cart_id_unique_checking' => [
                'cart_id: "provide_non_unique_id"',
                'Specified parameter "cart_id" is non unique.'
            ],
            'cart_id_length_checking' => [
                'cart_id: "provide_hash_with_prefix"',
                '"cart_id" length have to be less than or equal to 32.'
            ],
        ];
    }
}
