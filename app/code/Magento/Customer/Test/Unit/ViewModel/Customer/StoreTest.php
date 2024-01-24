<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\ViewModel\Customer;

use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\ViewModel\Customer\Store as CustomerStore;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

/**
 * Test for customer's store view model
 */
class StoreTest extends TestCase
{
    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /**
     * @var CustomerStore
     */
    private $customerStore;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var ConfigShare
     */
    protected $configShare;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    protected function setUp(): void
    {
        $this->systemStore = $this->createMock(SystemStore::class);
        $this->store = $this->createMock(Store::class);
        $this->configShare = $this->createMock(ConfigShare::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->dataPersistor = $this->getMockForAbstractClass(DataPersistorInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->customerStore = $this->objectManagerHelper->getObject(
            CustomerStore::class,
            [
                'systemStore' => $this->systemStore,
                'configShare' => $this->configShare,
                'storeManager' => $this->storeManager,
                'dataPersistor' => $this->dataPersistor
            ]
        );
    }

    /**
     * Test that method return correct array of options
     *
     * @param array $options
     * @param bool $isWebsiteScope
     * @param bool $isCustomerDataInSession
     * @dataProvider dataProviderOptionsArray
     * @return void
     */
    public function testToOptionArray(array $options, bool $isWebsiteScope, bool $isCustomerDataInSession): void
    {
        $this->configShare->method('isWebsiteScope')
            ->willReturn($isWebsiteScope);
        $this->store->method('getWebsiteId')
            ->willReturn(1);

        $websiteMock = $this->createPartialMock(Website::class, ['getId']);
        $websiteMock->method('getId')->willReturn(1);
        $this->systemStore->method('getWebsiteCollection')->willReturn([$websiteMock]);

        if ($isCustomerDataInSession) {
            $this->dataPersistor->method('get')
                ->with('customer')
                ->willReturn([
                    'account' => ['website_id' => '1']
                ]);
        } else {
            $this->storeManager->method('getDefaultStoreView')
                ->willReturn($this->store);
        }

        $this->systemStore->method('getStoreData')
            ->willReturn($this->store);
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
                            'label' => '    Default Store View',
                            'value' => '1',
                        ]
                    ],
                    '__disableTmpl' => true,
                ]
            ]);

        $this->assertEquals($options, $this->customerStore->toOptionArray());
    }

    /**
     * Data provider for testToOptionArray test
     *
     * @return array
     */
    public function dataProviderOptionsArray(): array
    {
        return [
            [
                'options' => [
                    [
                        'label' => 'Main Website',
                        'value' => [],
                        '__disableTmpl' => true,
                        'website_id' => '1',
                    ],
                    [
                        'label' => 'Main Website',
                        'value' => [
                            [
                                'label' => '    Default Store View',
                                'value' => '1',
                                'website_id' => '1',
                            ]
                        ],
                        '__disableTmpl' => true,
                        'website_id' => '1',
                    ]
                ],
                'isWebsiteScope' => true,
                'isCustomerDataInSession' => false,
            ],
            [
                'options' => [
                    [
                        'label' => 'Main Website',
                        'value' => [],
                        '__disableTmpl' => true,
                        'website_id' => '1',
                    ],
                    [
                        'label' => 'Main Website',
                        'value' => [
                            [
                                'label' => '    Default Store View',
                                'value' => '1',
                                'website_id' => '1',
                            ]
                        ],
                        '__disableTmpl' => true,
                        'website_id' => '1',
                    ]
                ],
                'isWebsiteScope' => false,
                'isCustomerDataInSession' => false,
            ],
            [
                'options' => [
                    [
                        'label' => 'Main Website',
                        'value' => [],
                        '__disableTmpl' => true,
                        'website_id' => '1',
                    ],
                    [
                        'label' => 'Main Website',
                        'value' => [
                            [
                                'label' => '    Default Store View',
                                'value' => '1',
                                'website_id' => '1',
                            ]
                        ],
                        '__disableTmpl' => true,
                        'website_id' => '1',
                    ]
                ],
                'isWebsiteScope' => false,
                'isCustomerDataInSession' => true,
            ]
        ];
    }
}
