<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\CustomerData;

use Magento\Checkout\CustomerData\DirectoryData;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\Country;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DirectoryDataTest extends TestCase
{
    /**
     * @var DirectoryData
     */
    private $model;

    /**
     * @var HelperData|MockObject
     */
    private $directoryHelperMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * Setup environment for testing
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->directoryHelperMock = $this->createMock(HelperData::class);

        $this->model = $this->objectManager->getObject(
            DirectoryData::class,
            [
                'directoryHelper' => $this->directoryHelperMock
            ]
        );
    }

    /**
     * Test getSectionData() function
     */
    public function testGetSectionData()
    {
        $regions = [
            'US' => [
                'TX' => [
                    'code' => 'TX',
                    'name' => 'Texas'
                ]
            ]
        ];

        $testCountryInfo = $this->objectManager->getObject(Country::class);
        $testCountryInfo->setData('country_id', 'US');
        $testCountryInfo->setData('iso2_code', 'US');
        $testCountryInfo->setData('iso3_code', 'USA');
        $testCountryInfo->setData('name_default', 'United States of America');
        $testCountryInfo->setData('name_en_US', 'United States of America');
        $countries = ['US' => $testCountryInfo];

        $this->directoryHelperMock->expects($this->any())
            ->method('getRegionData')
            ->willReturn($regions);

        $this->directoryHelperMock->expects($this->any())
            ->method('getCountryCollection')
            ->willReturn($countries);

        /* Assert result */
        $this->assertEquals(
            [
                'US' => [
                    'name' => 'United States of America',
                    'regions' => [
                        'TX' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ]
                    ]
                ]
            ],
            $this->model->getSectionData()
        );
    }
}
