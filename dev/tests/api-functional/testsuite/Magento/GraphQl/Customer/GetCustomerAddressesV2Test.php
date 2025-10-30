<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\CustomerWithAddresses;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

#[
    DataFixture(CustomerWithAddresses::class, ['email' => 'customer@example.com',], 'customer')
]
class GetCustomerAddressesV2Test extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * Initialize fixture namespaces.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @param int $pageSize
     * @param int $currentPage
     * @param array $expectedResponse
     * @return void
     * @throws AuthenticationException
     * @dataProvider dataProviderGetCustomerAddressesV2
     */
    public function testGetCustomerAddressesV2(int $pageSize, int $currentPage, array $expectedResponse)
    {
        $query = $this->getQuery($pageSize, $currentPage);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        self::assertArrayHasKey('addressesV2', $response['customer']);
        $addressesV2 = $response['customer']['addressesV2'];
        self::assertNotEmpty($addressesV2);
        self::assertIsArray($addressesV2);
        self::assertEquals($expectedResponse['items_count'], count($addressesV2['items']));
        self::assertEquals($expectedResponse['total_count'], $addressesV2['total_count']);
        self::assertEquals($expectedResponse['page_info'], $addressesV2['page_info']);
    }

    public function testAddressesV2NotAuthorized()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $query = $this->getQuery();
        $this->graphQlQuery($query);
    }

    /**
     * @throws AuthenticationException
     * @throws Exception
     */
    #[
        DataFixture(Customer::class, ['email' => 'customer2@example.com',], 'customer2')
    ]
    public function testAddressesV2ForCustomerWithoutAddresses()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders('customer2@example.com', 'password')
        );
        $addressesV2 = $response['customer']['addressesV2'];
        $this->assertEmpty($addressesV2['items']);
        $this->assertEquals(0, $addressesV2['total_count']);
        $this->assertEquals(0, $addressesV2['page_info']['total_pages']);
    }

    /**
     * Data provider for customer address input
     *
     * @return array
     */
    public static function dataProviderGetCustomerAddressesV2(): array
    {
        return [
            'scenario_1' => [
                'pageSize' => 1,
                'currentPage' => 1,
                'expectedResponse' => [
                    'items_count' => 1,
                    'page_info' => [
                        'page_size' => 1,
                        'current_page' => 1,
                        'total_pages' => 3
                    ],
                    'total_count' => 3
                ]
            ],
            'scenario_2' => [
                'pageSize' => 2,
                'currentPage' => 1,
                'expectedResponse' => [
                    'items_count' => 2,
                    'page_info' => [
                        'page_size' => 2,
                        'current_page' => 1,
                        'total_pages' => 2
                    ],
                    'total_count' => 3
                ]
            ],
            'scenario_3' => [
                'pageSize' => 2,
                'currentPage' => 2,
                'expectedResponse' => [
                    'items_count' => 1,
                    'page_info' => [
                        'page_size' => 2,
                        'current_page' => 2,
                        'total_pages' => 2
                    ],
                    'total_count' => 3
                ]
            ],
            'scenario_4' => [
                'pageSize' => 3,
                'currentPage' => 1,
                'expectedResponse' => [
                    'items_count' => 3,
                    'page_info' => [
                        'page_size' => 3,
                        'current_page' => 1,
                        'total_pages' => 1
                    ],
                    'total_count' => 3
                ]
            ]
        ];
    }

    /**
     * Get customer auth headers
     *
     * @param string $email
     * @param string $password
     * @return string[]
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Get addressesV2 query
     *
     * @param int $pageSize
     * @param int $currentPage
     * @return string
     */
    private function getQuery(int $pageSize = 5, int $currentPage = 1): string
    {
        return <<<QUERY
        {
          customer {
            email
            firstname
            lastname
            addressesV2 (
                pageSize: $pageSize
                currentPage: $currentPage
            ) {
                items {
                  id
                  customer_id
                  region_id
                  country_id
                  telephone
                  postcode
                  city
                  firstname
                  lastname
                }
                page_info {
                    page_size
                    current_page
                    total_pages
                }
                total_count
            }
          }
        }
        QUERY;
    }
}
