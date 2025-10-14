<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Config\Source;

use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AllRegionTest extends TestCase
{
    /**
     * @var \Magento\Directory\Model\Config\Source\AllRegion
     */
    protected $model;

    /**
     * @var Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $countryCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->addMethods(['__wakeup', '__sleep'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryCollection = $this->getMockBuilder(
            Collection::class
        )->onlyMethods(['load', 'toOptionArray', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()
            ->getMock();
        $countryCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->countryCollection);
        $this->countryCollection->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $regionCollectionFactory = $this->getMockBuilder(
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class
        )->disableOriginalConstructor()
            ->addMethods(['__wakeup', '__sleep'])
            ->onlyMethods(['create'])
            ->getMock();
        $this->regionCollection = $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Region\Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getIterator', '__wakeup', '__sleep'])
            ->getMock();
        $regionCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->regionCollection);
        $this->regionCollection->expects($this->once())
            ->method('load')
            ->willReturn($this->regionCollection);

        $this->model = $objectManagerHelper->getObject(
            Allregion::class,
            [
                'countryCollectionFactory' => $countryCollectionFactory,
                'regionCollectionFactory' => $regionCollectionFactory
            ]
        );
    }

    /**
     * @dataProvider toOptionArrayDataProvider
     * @param bool $isMultiselect
     * @param array $countries
     * @param array $regions
     * @param array $expectedResult
     */
    public function testToOptionArray($isMultiselect, $countries, $regions, $expectedResult)
    {
        $newRegions = [];
        foreach ($regions as $region)
        {
            if(is_callable($region))
            {
                $newRegions[] = $region($this);
            }
            else
            {
                $newRegions[] = $region;
            }
        }
        $this->countryCollection->expects($this->once())
            ->method('toOptionArray')
            ->with(false)
            ->willReturn(new \ArrayIterator($countries));
        $this->regionCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($newRegions));

        $this->assertEquals($expectedResult, $this->model->toOptionArray($isMultiselect));
    }

    /**
     * Return data sets for testToOptionArray()
     *
     * @return array
     */
    public static function toOptionArrayDataProvider()
    {
        return [
            [
                false,
                [
                    self::generateCountry('France', 'fr'),
                ],
                [
                    static fn (self $testCase) => $testCase->generateRegion('fr', 1, 'Paris')
                ],
                [
                    [
                        'label' => '',
                        'value' => '',
                    ],
                    [
                        'label' => 'France',
                        'value' => [
                            [
                                'label' => 'Paris',
                                'value' => 1,
                            ],
                        ]
                    ]
                ],
            ],
            [
                true,
                [
                    self::generateCountry('France', 'fr'),
                ],
                [
                    static fn (self $testCase) => $testCase->generateRegion('fr', 1, 'Paris'),
                    static fn (self $testCase) => $testCase->generateRegion('fr', 2, 'Marseille')
                ],
                [
                    [
                        'label' => 'France',
                        'value' => [
                            [
                                'label' => 'Paris',
                                'value' => 1,
                            ],
                            [
                                'label' => 'Marseille',
                                'value' => 2
                            ],
                        ],
                    ]
                ]
            ],
            [
                true,
                [
                    self::generateCountry('France', 'fr'),
                    self::generateCountry('Germany', 'de'),
                ],
                [
                    static fn (self $testCase) => $testCase->generateRegion('fr', 1, 'Paris'),
                    static fn (self $testCase) => $testCase->generateRegion('de', 2, 'Berlin')
                ],
                [
                    [
                        'label' => 'France',
                        'value' => [
                            [
                                'label' => 'Paris',
                                'value' => 1,
                            ],
                        ],
                    ],
                    [
                        'label' => 'Germany',
                        'value' => [
                            [
                                'label' => 'Berlin',
                                'value' => 2,
                            ],
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * Generate a country array.
     *
     * @param string $countryLabel
     * @param string $countryValue
     * @return array
     */
    private static function generateCountry($countryLabel, $countryValue)
    {
        return [
            'label' => $countryLabel,
            'value' => $countryValue
        ];
    }

    /**
     * Generate a mocked region.
     *
     * @param string $countryId
     * @param string $id
     * @param string $defaultName
     * @return Region
     */
    protected function generateRegion($countryId, $id, $defaultName)
    {
        $region = $this->getMockBuilder(Region::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCountryId','getDefaultName'])
            ->onlyMethods(['getId', '__wakeup', '__sleep'])
            ->getMock();
        $region->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $region->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $region->expects($this->once())
            ->method('getDefaultName')
            ->willReturn($defaultName);

        return $region;
    }
}
