<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Search;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad($query, $expectedResult)
    {
        /** Preconditions */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Backend\Model\Search\Config $configSearch */
        $configSearch = $objectManager->create(\Magento\Backend\Model\Search\Config::class);
        $configSearch->setQuery($query);
        $configSearch->load();

        /** SUT Execution */
        $searchResults = $configSearch->getResults();

        /** Ensure that search results are correct */
        $this->assertCount(count($expectedResult), $searchResults, 'Quantity of search result items is invalid.');
        foreach ($expectedResult as $itemIndex => $expectedItem) {
            /** Validate URL to item */
            $elementPathParts = explode('/', $expectedItem['id']);
            array_filter($elementPathParts, function ($value) {
                return $value !== '';
            });
            foreach ($elementPathParts as $elementPathPart) {
                $this->assertContains($elementPathPart, $searchResults[$itemIndex]['url'], 'Item URL is invalid.');
            }
            unset($searchResults[$itemIndex]['url']);

            /** Validate other item data */
            $this->assertEquals($expectedItem, $searchResults[$itemIndex], "Data of item #$itemIndex is invalid.");
        }
    }

    public static function loadDataProvider()
    {
        return [
            'Search by field name' => [
                'Store Name',
                [
                    [
                        'id'          => 'general/store_information/name',
                        'type'        => null,
                        'name'        => 'Store Name',
                        'description' => '/ General / General / Store Information',
                    ],
                ],
            ],
            'Search by field name, multiple items result' => [
                'Secure Base URL',
                [
                    [
                        'id'          => 'web/secure/base_url',
                        'type'        => null,
                        'name'        => 'Secure Base URL',
                        'description' => '/ General / Web / Base URLs (Secure)',
                    ],
                    [
                        'id'          => 'web/secure/base_static_url',
                        'type'        => null,
                        'name'        => 'Secure Base URL for Static View Files',
                        'description' => '/ General / Web / Base URLs (Secure)',
                    ],
                    [
                        'id'          => 'web/secure/base_media_url',
                        'type'        => null,
                        'name'        => 'Secure Base URL for User Media Files',
                        'description' => '/ General / Web / Base URLs (Secure)',
                    ],
                ],
            ],
            'Search by group name' => [
                'Country Options',
                [
                    [
                        'id'          => 'general/country',
                        'type'        => null,
                        'name'        => 'Country Options',
                        'description' => '/ General / General',
                    ],
                ],
            ],
            'Search by section name' => [
                'Currency Setup',
                [
                    [
                        'id'          => '/currency',
                        'type'        => null,
                        'name'        => 'Currency Setup',
                        'description' => '/ General',
                    ],
                ],
            ],
        ];
    }
}
