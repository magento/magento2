<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Exception;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test that order cancellation are returned via the storeConfig GraphQL query.
 * Query must take into account passed store through request header.
 * Reasons are returned following the same order as they are stored in the DB.
 */
class OrderCancellationReasonsTest extends GraphQlAbstract
{
    private const STORE_CONFIG_QUERY = <<<QUERY
 {
  storeConfig {
    order_cancellation_reasons {
      description
    }
  }
}
QUERY;

    /**
     * When no cancellation reasons are provided through `sales/cancellation/reasons` configuration
     * the query returns the default cancellation reasons from `OrderCancellation/etc/config.xml` file.
     *
     * @return void
     * @throws Exception
     */
    #[
        DbIsolation(true)
    ]
    public function testGetDefaultCancellationReasons()
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);

        $this->assertEquals(
            [
                'storeConfig' => [
                    'order_cancellation_reasons' => [
                        [
                            'description' => 'The item(s) are no longer needed'
                        ],
                        [
                            'description' => 'The order was placed by mistake'
                        ],
                        [
                            'description' => 'Item(s) not arriving within the expected timeframe'
                        ],
                        [
                            'description' => 'Found a better price elsewhere'
                        ],
                        [
                            'description' => 'Other'
                        ]
                    ],
                ]
            ],
            $response
        );
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, [
            'store_group_id' => '$store_group2.id$',
            'code' => 'some_store_2',
            'name' => 'Some Store 2'
        ], 'store2'),
        Config(
            'sales/cancellation/reasons',
            '{"Reason1":{"description":"Reason 1"},"110":{"description":"Reason 2"},"111":{"description":"Another"}}',
            'store',
            'some_store_2'
        )
    ]
    public function testGetCancellationReasonsSetUpThroughConfiguration()
    {
        /** @var StoreInterface $store */
        $store = DataFixtureStorageManager::getStorage()->get('store2');

        $response = $this->graphQlQuery(
            self::STORE_CONFIG_QUERY,
            [],
            '',
            ['Store' => $store->getCode()]
        );

        $this->assertEquals(
            [
                'storeConfig' => [
                    'order_cancellation_reasons' => [
                        [
                            'description' => 'Reason 1'
                        ],
                        [
                            'description' => 'Reason 2'
                        ],
                        [
                            'description' => 'Another'
                        ]
                    ],
                ]
            ],
            $response
        );
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website3'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website3.id$'], 'store_group3'),
        DataFixture(StoreFixture::class, [
            'store_group_id' => '$store_group3.id$',
            'code' => 'some_store_3',
            'name' => 'Some Store 3'
        ], 'store3'),
        Config(
            'sales/cancellation/reasons',
            '{"Reason1": {"description": "Dummy reason"}}',
            'store',
            'some_store_3'
        )
    ]
    public function testGetCancellationReasonsForDifferentStore()
    {
        /** @var StoreInterface $store */
        $store = DataFixtureStorageManager::getStorage()->get('store3');

        $response = $this->graphQlQuery(
            self::STORE_CONFIG_QUERY,
            [],
            '',
            ['Store' => $store->getCode()]
        );

        $this->assertEquals(
            [
                'storeConfig' => [
                    'order_cancellation_reasons' => [
                        [
                            'description' => 'Dummy reason'
                        ]
                    ],
                ]
            ],
            $response
        );
    }
}
