<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\ViewModel\Customer;

use Magento\Customer\ViewModel\Customer\Store as CustomerStore;
use Magento\Store\Model\Store;
use Magento\Store\Model\System\Store as SystemStore;
use PHPUnit\Framework\TestCase;

/**
 * Test for customer's store view model
 */
class StoreTest extends TestCase
{
    /**
     * @var CustomerStore
     */
    private $customerStore;

    /**
     * @var SystemStore
     */
    private $systemStore;

    protected function setUp(): void
    {
        $this->systemStore = $this->createStub(SystemStore::class);
        $this->customerStore = new CustomerStore($this->systemStore);
    }

    /**
     * Each option (website header, group and nested store view) is stamped with its real
     * website id in a single pass, regardless of the customer account sharing configuration.
     *
     * @return void
     */
    public function testToOptionArrayAddsRealWebsiteIdInSinglePass(): void
    {
        $this->systemStore->method('getStoreData')
            ->willReturnMap([
                ['1', $this->createStore(1)],
            ]);
        $this->systemStore->method('getStoreValuesForForm')
            ->willReturn([
                [
                    'label' => 'Main Website',
                    'value' => [],
                    '__disableTmpl' => true,
                ],
                [
                    'label' => 'Main Website',
                    'value' => [
                        [
                            'label' => '    Default Store View',
                            'value' => '1',
                        ],
                    ],
                    '__disableTmpl' => true,
                ],
            ]);

        $expected = [
            [
                'label' => 'Main Website',
                'value' => [],
                '__disableTmpl' => true,
                'website_id' => 1,
            ],
            [
                'label' => 'Main Website',
                'value' => [
                    [
                        'label' => '    Default Store View',
                        'value' => '1',
                        'website_id' => 1,
                    ],
                ],
                '__disableTmpl' => true,
                'website_id' => 1,
            ],
        ];

        $this->assertEquals($expected, $this->customerStore->toOptionArray());
    }

    /**
     * The options must not be multiplied by the number of websites: with two websites the result
     * contains one entry per source option (each tagged with its own website id), not
     * websites x options. This guards against the O(websites x options) payload regression.
     *
     * @return void
     */
    public function testToOptionArrayDoesNotDuplicateOptionsAcrossWebsites(): void
    {
        $this->systemStore->method('getStoreData')
            ->willReturnMap([
                ['1', $this->createStore(1)],
                ['2', $this->createStore(2)],
            ]);
        $this->systemStore->method('getStoreValuesForForm')
            ->willReturn([
                ['label' => 'Website One', 'value' => [], '__disableTmpl' => true],
                [
                    'label' => '    Group One',
                    'value' => [
                        ['label' => '        Store View One', 'value' => '1'],
                    ],
                    '__disableTmpl' => true,
                ],
                ['label' => 'Website Two', 'value' => [], '__disableTmpl' => true],
                [
                    'label' => '    Group Two',
                    'value' => [
                        ['label' => '        Store View Two', 'value' => '2'],
                    ],
                    '__disableTmpl' => true,
                ],
            ]);
        $expected = [
            ['label' => 'Website One', 'value' => [], '__disableTmpl' => true, 'website_id' => 1],
            [
                'label' => '    Group One',
                'value' => [
                    ['label' => '        Store View One', 'value' => '1', 'website_id' => 1],
                ],
                '__disableTmpl' => true,
                'website_id' => 1,
            ],
            ['label' => 'Website Two', 'value' => [], '__disableTmpl' => true, 'website_id' => 2],
            [
                'label' => '    Group Two',
                'value' => [
                    ['label' => '        Store View Two', 'value' => '2', 'website_id' => 2],
                ],
                '__disableTmpl' => true,
                'website_id' => 2,
            ],
        ];
        $result = $this->customerStore->toOptionArray();
        $this->assertCount(4, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * Create a store mock resolving to the given website id.
     *
     * @param int $websiteId
     * @return Store
     */
    private function createStore(int $websiteId): Store
    {
        $store = $this->createStub(Store::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        return $store;
    }
}
