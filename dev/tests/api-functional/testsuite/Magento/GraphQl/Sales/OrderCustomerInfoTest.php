<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for orders.items.customer_info
 */
class OrderCustomerInfoTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test the OrderCustomerInfo data for customerOrders query
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['type_id' => 'virtual'], as: 'product'),
        DataFixture(
            CustomerFixture::class,
            [
                'firstname' => 'First Name',
                'lastname' => 'Last Name',
                'middlename' => 'Middle Name',
                'prefix' => 'MR'
            ],
            as: 'customer'
        ),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            PlaceOrderFixture::class,
            [
                'cart_id' => '$quote.id$',
            ],
            'order1'
        ),
    ]
    public function testOrderCustomerInfoField()
    {
        $query = $this->getCustomerOrdersQuery();
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($customerEmail, 'password'));
        $data = $response['customer']['orders']['items'];
        $firstItem = $data[0];
        $expected = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'prefix' => 'MR',
            'middlename' => 'Middle Name',
            'suffix' => null
        ];
        self::assertEquals($expected, $firstItem['customer_info']);
    }

    /**
     * Get Customer Orders query with customerInfo field
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
query {
    customer {
        orders(pageSize: 10) {
            total_count
            items {
                customer_info {
                    firstname
                    lastname
                    middlename
                    prefix
                    suffix
                }
            }
        }
    }
}
QUERY;
    }

    /**
     * Returns the header with customer token for GQL Mutation
     *
     * @param  string $email
     * @param  string $password
     * @return array
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
