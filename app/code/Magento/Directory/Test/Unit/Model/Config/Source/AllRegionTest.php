<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Config\Source;

class AllRegionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Config\Source\AllRegion
     */
    protected $model;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $countryCollectionFactory = $this->getMockBuilder(
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class
        )->setMethods(['create', '__wakeup', '__sleep'])->disableOriginalConstructor()->getMock();

        $this->countryCollection = $this->getMockBuilder(
            \Magento\Directory\Model\ResourceModel\Country\Collection::class
        )->setMethods(['load', 'toOptionArray', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()
            ->getMock();
        $countryCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->countryCollection));
        $this->countryCollection->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());

        $regionCollectionFactory = $this->getMockBuilder(
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class
        )->disableOriginalConstructor()->setMethods(['create', '__wakeup', '__sleep'])->getMock();
        $this->regionCollection = $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Region\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getIterator', '__wakeup', '__sleep'])
            ->getMock();
        $regionCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->regionCollection));
        $this->regionCollection->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->regionCollection));

        $this->model = $objectManagerHelper->getObject(
            \Magento\Directory\Model\Config\Source\Allregion::class,
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
        $this->countryCollection->expects($this->once())
            ->method('toOptionArray')
            ->with(false)
            ->will($this->returnValue(new \ArrayIterator($countries)));
        $this->regionCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($regions)));

        $this->assertEquals($expectedResult, $this->model->toOptionArray($isMultiselect));
    }

    /**
     * Return data sets for testToOptionArray()
     *
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        return [
            [
                false,
                [
                    $this->generateCountry('France', 'fr'),
                ],
                [
                    $this->generateRegion('fr', 1, 'Paris')
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
                    $this->generateCountry('France', 'fr'),
                ],
                [
                    $this->generateRegion('fr', 1, 'Paris'),
                    $this->generateRegion('fr', 2, 'Marseille')
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
                    $this->generateCountry('France', 'fr'),
                    $this->generateCountry('Germany', 'de'),
                ],
                [
                    $this->generateRegion('fr', 1, 'Paris'),
                    $this->generateRegion('de', 2, 'Berlin')
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
    private function generateCountry($countryLabel, $countryValue)
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
     * @return \Magento\Directory\Model\Region
     */
    private function generateRegion($countryId, $id, $defaultName)
    {
        $region = $this->getMockBuilder(\Magento\Directory\Model\Region::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId', 'getId', 'getDefaultName', '__wakeup', '__sleep'])
            ->getMock();
        $region->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue($countryId));
        $region->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        $region->expects($this->once())
            ->method('getDefaultName')
            ->will($this->returnValue($defaultName));

        return $region;
    }
}
