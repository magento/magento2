<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetCustomerAddressesTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
    }

    /**
     * @throws AuthenticationException
     */
    #[
        DataFixture(
            Customer::class,
            [
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'default_billing' => true,
                        'default_shipping' => true
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testGetCustomerDetailsWithAddress(): void
    {
        $address = current($this->customer->getAddresses());

        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $this->customer->getFirstname(),
                    'lastname' => $this->customer->getLastname(),
                    'email' => $this->customer->getEmail(),
                    'addresses' => [
                        [
                            'uid' => $this->idEncoder->encode((string) $address->getId()),
                            'country_id' => $address->getCountryId()
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerAddressQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($this->customer->getEmail())
            )
        );
    }

    /**
     * @throws AuthenticationException
     */
    #[
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testGetCustomerDetailsWithoutAddress(): void
    {
        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $this->customer->getFirstname(),
                    'lastname' => $this->customer->getLastname(),
                    'email' => $this->customer->getEmail(),
                    'addresses' => []
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerAddressQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($this->customer->getEmail())
            )
        );
    }

    /**
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Get Customer addresses query
     *
     * @return string
     */
    private function getCustomerAddressQuery(): string
    {
        return <<<QUERY
           query {
              customer {
                firstname
                lastname
                email
                addresses {
                   uid
                   country_id
                }
              }
           }
        QUERY;
    }
}
