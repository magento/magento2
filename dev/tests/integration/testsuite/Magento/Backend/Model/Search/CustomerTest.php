<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model\Search;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
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
                        'description' => 'CompanyName'
                    ],
                    [
                        'id' => 'customer/1/2',
                        'type' => 'Customer',
                        'name' => 'Firstname2 Lastname2',
                        'description' => null
                    ]
                ]
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
                        'description' => null
                    ]
                ]
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
                        'description' => null
                    ]
                ]
            ],
            'No results' => [
                'NotExistingCustomerName',
                10, // Items on page
                1, // Page number
                []
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
                        'description' => 'CompanyName'
                    ],
                ]
            ],
        ];
    }
}
