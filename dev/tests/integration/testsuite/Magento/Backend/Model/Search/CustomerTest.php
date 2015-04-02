<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Search;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Customer/_files/three_customers.php
 * @magentoDataFixture Magento/Customer/_files/customer_address.php
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad($query, $limit, $start, $expectedResult)
    {
        /** Preconditions */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Backend\Model\Search\Customer $customerSearch */
        $customerSearch = $objectManager->create('Magento\Backend\Model\Search\Customer');
        $customerSearch->setQuery($query);
        $customerSearch->setLimit($limit);
        $customerSearch->setStart($start);
        $customerSearch->load();

        /** SUT Execution */
        $searchResults = $customerSearch->getResults();

        /** Ensure that search results are correct */
        $this->assertCount(count($expectedResult), $searchResults, 'Quantity of search result items is invalid.');
        foreach ($expectedResult as $itemIndex => $expectedItem) {
            /** Validate URL to item */
            $customerId = substr($expectedItem['id'], 11); // 'customer/1/' is added to all actual customer IDs
            $this->assertContains(
                "customer/index/edit/id/$customerId",
                $searchResults[$itemIndex]['url'],
                'Item URL is invalid.'
            );
            unset($searchResults[$itemIndex]['url']);

            /** Validate other item data */
            $this->assertEquals($expectedItem, $searchResults[$itemIndex], "Data of item #$itemIndex is invalid.");
        }
    }

    public static function loadDataProvider()
    {
        return [
            'All items, first page' => [
                'Firstname',
                2, // Items on page
                1, // Page number
                [
                    [
                        'id' => 'customer/1/1',
                        'type' => 'Customer',
                        'name' => 'Firstname Lastname',
                        'description' => 'CompanyName',
                    ],
                    [
                        'id' => 'customer/1/2',
                        'type' => 'Customer',
                        'name' => 'Firstname2 Lastname2',
                        'description' => null
                    ]
                ],
            ],
            'All items, second page' => [
                'Firstname',
                2, // Items on page
                2, // Page number
                [
                    [
                        'id' => 'customer/1/3',
                        'type' => 'Customer',
                        'name' => 'Firstname3 Lastname3',
                        'description' => null,
                    ]
                ],
            ],
            'Search by last name, second item only' => [
                'Lastname2',
                10, // Items on page
                1, // Page number
                [
                    [
                        'id' => 'customer/1/2',
                        'type' => 'Customer',
                        'name' => 'Firstname2 Lastname2',
                        'description' => null,
                    ]
                ],
            ],
            'No results' => [
                'NotExistingCustomerName',
                10, // Items on page
                1, // Page number
                [],
            ],
            'Search by company name, first item only' => [
                'CompanyName',
                10, // Items on page
                1, // Page number
                [
                    [
                        'id' => 'customer/1/1',
                        'type' => 'Customer',
                        'name' => 'Firstname Lastname',
                        'description' => 'CompanyName',
                    ],
                ],
            ],
        ];
    }
}
