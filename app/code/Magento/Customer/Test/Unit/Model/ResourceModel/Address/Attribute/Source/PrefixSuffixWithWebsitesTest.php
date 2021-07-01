<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Source;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Options as CustomerOptions;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\PrefixSuffixWithWebsites;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Magento\Customer\Model\ResourceModel\Address\Attribute\Source\PrefixSuffixWithWebsites
 */
class PrefixSuffixWithWebsitesTest extends TestCase
{
    /**
     * @var PrefixSuffixWithWebsites
     */
    private $model;

    /**
     * @var CustomerOptions|MockObject
     */
    private $customerOptionsMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Share|MockObject
     */
    private $shareConfigMock;

    /**
     * @var AddressHelper|MockObject
     */
    private $addressHelperMock;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->customerOptionsMock = $this->getMockBuilder(CustomerOptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->shareConfigMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressHelperMock = $this->getMockBuilder(AddressHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            PrefixSuffixWithWebsites::class,
            [
                'customerOptions' => $this->customerOptionsMock,
                'storeManager' => $this->storeManagerMock,
                'shareConfig' => $this->shareConfigMock,
                'addressHelper' => $this->addressHelperMock
            ]
        );
    }

    /**
     * @dataProvider getAllOptionsProvider
     * @param $attributeCode
     * @param $options
     * @param $result
     */
    public function testGetAllOptions($attributeCode, $options, $result)
    {
        $this->initStores();

        $optionsMap = [];
        foreach ($options as $storeId => $optionsStore) {
            $optionsMap[] = [$storeId, $optionsStore];
        }
        if ($attributeCode === 'prefix') {
            $this->customerOptionsMock->method('getNamePrefixOptions')
                ->willReturnMap($optionsMap);
        } elseif ($attributeCode === 'suffix') {
            $this->customerOptionsMock->method('getNameSuffixOptions')
                ->willReturnMap($optionsMap);
        }

        $this->assertEquals(
            $result,
            $this->model->getAllOptions($attributeCode)
        );
    }

    /**
     * @dataProvider getIsRequiredProvider
     * @param string $attributeCode
     * @param array $isRequired
     * @param array $result
     * @throws NoSuchEntityException
     */
    public function testGetIsRequired($attributeCode, $isRequired, $result)
    {
        $this->initStores();

        $isRequiredMap = [];
        foreach ($isRequired as $storeId => $isRequiredStore) {
            $isRequiredMap[] = [$attributeCode . '_show', $storeId, $isRequiredStore];
        }

        $this->addressHelperMock->method('getConfig')
            ->willReturnMap($isRequiredMap);

        $this->assertEquals(
            $result,
            $this->model->getIsRequired($attributeCode)
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAllOptionsProvider()
    {
        return [
            [
                'prefix',
                [
                    1 => false,
                    2 => false
                ],
                []
            ],
            [
                'prefix',
                [
                    1 => false,
                    2 => [
                        ' ' => ' ',
                        'mr' => 'mr',
                        'mrs' => 'mrs'
                    ]
                ],
                [
                    [
                        'label' => ' ',
                        'value' => '',
                        'website_ids' => [
                            2
                        ]
                    ],
                    [
                        'label' => 'mr',
                        'value' => 'mr',
                        'website_ids' => [
                            2
                        ]
                    ],
                    [
                        'label' => 'mrs',
                        'value' => 'mrs',
                        'website_ids' => [
                            2
                        ]
                    ]
                ]
            ],
            [
                'suffix',
                [
                    1 => [
                        ' ' => ' ',
                        'jr' => 'jr',
                        'sr' => 'sr'
                    ],
                    2 => [
                        ' ' => ' ',
                        'jr2' => 'jr2',
                        'sr' => 'sr'
                    ]
                ],
                [
                    [
                        'label' => ' ',
                        'value' => '',
                        'website_ids' => [
                            1,
                            2
                        ]
                    ],
                    [
                        'label' => 'jr',
                        'value' => 'jr',
                        'website_ids' => [
                            1
                        ]
                    ],
                    [
                        'label' => 'sr',
                        'value' => 'sr',
                        'website_ids' => [
                            1,
                            2
                        ]
                    ],
                    [
                        'label' => 'jr2',
                        'value' => 'jr2',
                        'website_ids' => [
                            2
                        ]
                    ]
                ]
            ],
            [
                'suffix',
                [
                    1 => [
                        ' ' => ' ',
                        'jr' => 'jr',
                        'sr' => 'sr'
                    ],
                    2 => false
                ],
                [
                    [
                        'label' => ' ',
                        'value' => '',
                        'website_ids' => [
                            1
                        ]
                    ],
                    [
                        'label' => 'jr',
                        'value' => 'jr',
                        'website_ids' => [
                            1
                        ]
                    ],
                    [
                        'label' => 'sr',
                        'value' => 'sr',
                        'website_ids' => [
                            1
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function getIsRequiredProvider()
    {
        return [
            [
                'prefix',
                [
                    1 => Nooptreq::VALUE_REQUIRED,
                    2 => Nooptreq::VALUE_OPTIONAL
                ],
                [
                    1 => true,
                    2 => false
                ]
            ],
            [
                'prefix',
                [
                    1 => Nooptreq::VALUE_OPTIONAL,
                    2 => Nooptreq::VALUE_OPTIONAL
                ],
                [
                    1 => false,
                    2 => false
                ]
            ],
            [
                'suffix',
                [
                    1 => Nooptreq::VALUE_OPTIONAL,
                    2 => Nooptreq::VALUE_REQUIRED
                ],
                [
                    1 => false,
                    2 => true
                ]
            ],
            [
                'suffix',
                [
                    1 => Nooptreq::VALUE_REQUIRED,
                    2 => Nooptreq::VALUE_REQUIRED
                ],
                [
                    1 => true,
                    2 => true
                ]
            ]
        ];
    }

    private function initStores()
    {
        $this->shareConfigMock->method('isGlobalScope')
            ->willReturn(false);

        $websites = [];
        $groupsMap = [];

        foreach ([1, 2] as $id) {
            $websiteMock = $this->getMockBuilder(Website::class)
                ->disableOriginalConstructor()
                ->getMock();
            $websiteMock->method('getId')
                ->willReturn($id);
            $websiteMock->method('getDefaultGroupId')
                ->willReturn($id);

            $groupMock = $this->getMockBuilder(Group::class)
                ->disableOriginalConstructor()
                ->getMock();

            $groupMock->method('getDefaultStoreId')
                ->willReturn($id);

            $websites[] = $websiteMock;
            $groupsMap[] = [$id, $groupMock];
        }

        $this->storeManagerMock->method('getWebsites')
            ->willReturn($websites);

        $this->storeManagerMock->method('getGroup')
            ->willReturnMap($groupsMap);
    }
}
