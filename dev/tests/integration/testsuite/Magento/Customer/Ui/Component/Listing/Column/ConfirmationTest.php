<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Customer\Ui\Component\Listing\Column\Confirmation.
 */
class ConfirmationTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var Confirmation
     */
    private $confirmation;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->confirmation = Bootstrap::getObjectManager()->create(
            Confirmation::class,
            [
                'components' => [],
                'data' => ['name' => 'confirmation'],
            ]
        );
    }

    /**
     * Verify Confirmation::prepareDataSource() won't throw exception in case requested website doesn't exist.
     *
     * @param array $customerDataSource
     * @param array $expectedResult
     * @magentoConfigFixture base_website customer/create_account/confirm 1
     * @dataProvider customersDataProvider
     *
     * @return void
     */
    public function testPrepareDataSource(array $customerDataSource, array $expectedResult): void
    {
        $result = $this->confirmation->prepareDataSource($customerDataSource);

        self::assertEquals($expectedResult, $result);
    }

    /**
     * CustomerDataSource data provider.
     *
     * @return array
     */
    public function customersDataProvider(): array
    {
        return [
            [
                'customerDataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'id_field_name' => 'entity_id',
                                'entity_id' => '1',
                                'name' => 'John Doe',
                                'email' => 'john.doe@example.com',
                                'group_id' => ['1'],
                                'created_at' => '2020-12-28 07:05:50',
                                'website_id' => ['1'],
                                'confirmation' => false,
                                'created_in' => 'Default Store View',
                            ],
                            [
                                'id_field_name' => 'entity_id',
                                'entity_id' => '2',
                                'name' => 'Jane Doe',
                                'email' => 'jane.doe@example.com',
                                'group_id' => ['1'],
                                'created_at' => '2020-12-28 07:06:17',
                                'website_id' => ['999999999'],
                                'confirmation' => null,
                                'created_in' => 'CustomStoreViewWhichDoesNotExistAnymore',
                            ],
                        ],
                        'totalRecords' => 2,
                    ],
                ],
                'expectedResult' => [
                    'data' => [
                        'items' => [
                            [
                                'id_field_name' => 'entity_id',
                                'entity_id' => '1',
                                'name' => 'John Doe',
                                'email' => 'john.doe@example.com',
                                'group_id' => ['1'],
                                'created_at' => '2020-12-28 07:05:50',
                                'website_id' => ['1'],
                                'confirmation' => __('Confirmation Required'),
                                'created_in' => 'Default Store View',
                            ],
                            [
                                'id_field_name' => 'entity_id',
                                'entity_id' => '2',
                                'name' => 'Jane Doe',
                                'email' => 'jane.doe@example.com',
                                'group_id' => ['1'],
                                'created_at' => '2020-12-28 07:06:17',
                                'website_id' => ['999999999'],
                                'confirmation' =>  __('Confirmed'),
                                'created_in' => 'CustomStoreViewWhichDoesNotExistAnymore',
                            ],
                        ],
                        'totalRecords' => 2,
                    ],
                ],
            ],
        ];
    }
}
