<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryInformationAcquirer;
use Magento\Directory\Model\Data\CountryInformation;
use Magento\Directory\Model\Data\CountryInformationFactory;
use Magento\Directory\Model\Data\RegionInformation;
use Magento\Directory\Model\Data\RegionInformationFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryInformationAcquirerTest extends TestCase
{
    /**
     * @var CountryInformationAcquirer
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $countryInformationFactory;

    /**
     * @var MockObject
     */
    protected $regionInformationFactory;

    /**
     * @var MockObject
     */
    protected $storeManager;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $className = CountryInformationFactory::class;
        $this->countryInformationFactory = $this->createPartialMock($className, ['create']);

        $className = RegionInformationFactory::class;
        $this->regionInformationFactory = $this->createPartialMock($className, ['create']);

        $className = Data::class;
        $this->directoryHelper = $this->createPartialMock($className, ['getCountryCollection', 'getRegionData']);

        $className = StoreManager::class;
        $this->storeManager = $this->createPartialMock($className, ['getStore']);

        $this->model = $this->objectManager->getObject(
            CountryInformationAcquirer::class,
            [
                'countryInformationFactory' => $this->countryInformationFactory,
                'regionInformationFactory' => $this->regionInformationFactory,
                'directoryHelper' => $this->directoryHelper,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * test GetCountriesInfo
     */
    public function testGetCountriesInfo()
    {
        /** @var Store $store */
        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $testCountryInfo = $this->objectManager->getObject(Country::class);
        $testCountryInfo->setData('country_id', 'US');
        $testCountryInfo->setData('iso2_code', 'US');
        $testCountryInfo->setData('iso3_code', 'USA');
        $testCountryInfo->setData('name_default', 'United States of America');
        $testCountryInfo->setData('name_en_US', 'United States of America');
        $countries = ['US' => $testCountryInfo];
        $this->directoryHelper->expects($this->once())->method('getCountryCollection')->willReturn($countries);

        $regions = ['US' => ['TX' => ['code' => 'TX', 'name' => 'Texas']]];
        $this->directoryHelper->expects($this->once())->method('getRegionData')->willReturn($regions);

        $countryInfo = $this->objectManager->getObject(CountryInformation::class);
        $this->countryInformationFactory->expects($this->once())->method('create')->willReturn($countryInfo);

        $regionInfo = $this->objectManager->getObject(RegionInformation::class);
        $this->regionInformationFactory->expects($this->once())->method('create')->willReturn($regionInfo);

        $result = $this->model->getCountriesInfo();
        $this->assertEquals('US', $result[0]->getId());
        $this->assertEquals('US', $result[0]->getTwoLetterAbbreviation());
        $this->assertEquals('USA', $result[0]->getThreeLetterAbbreviation());
        $this->assertEquals('United States of America', $result[0]->getFullNameLocale());
        $this->assertEquals('United States of America', $result[0]->getFullNameEnglish());

        $regionResult = $result[0]->getAvailableRegions();
        $this->assertEquals('TX', $regionResult[0]->getCode());
        $this->assertEquals('Texas', $regionResult[0]->getName());
    }

    /**
     * test GetGetCountryInfo
     */
    public function testGetCountryInfo()
    {
        /** @var Store $store */
        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $testCountryInfo = $this->objectManager->getObject(Country::class);
        $testCountryInfo->setData('country_id', 'AE');
        $testCountryInfo->setData('iso2_code', 'AE');
        $testCountryInfo->setData('iso3_code', 'ARE');
        $testCountryInfo->setData('name_default', 'United Arab Emirates');
        $testCountryInfo->setData('name_en_US', 'United Arab Emirates');

        $countryCollection = $this->createMock(Collection::class);
        $countryCollection->expects($this->once())->method('load')->willReturnSelf();
        $countryCollection->expects($this->once())->method('getItemById')->with('AE')->willReturn($testCountryInfo);

        $this->directoryHelper->expects($this->once())->method('getCountryCollection')->willReturn($countryCollection);
        $this->directoryHelper->expects($this->once())->method('getRegionData')->willReturn([]);

        $countryInfo = $this->objectManager->getObject(CountryInformation::class);
        $this->countryInformationFactory->expects($this->once())->method('create')->willReturn($countryInfo);

        $result = $this->model->getCountryInfo('AE');
        $this->assertEquals('AE', $result->getId());
        $this->assertEquals('AE', $result->getTwoLetterAbbreviation());
        $this->assertEquals('ARE', $result->getThreeLetterAbbreviation());
        $this->assertEquals('United Arab Emirates', $result->getFullNameLocale());
        $this->assertEquals('United Arab Emirates', $result->getFullNameEnglish());
    }

    /**
     * test GetGetCountryInfoNotFound
     */
    public function testGetCountryInfoNotFound()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The country isn\'t available.');
        /** @var Store $store */
        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $testCountryInfo = $this->objectManager->getObject(Country::class);
        $testCountryInfo->setData('country_id', 'AE');
        $testCountryInfo->setData('iso2_code', 'AE');
        $testCountryInfo->setData('iso3_code', 'ARE');
        $testCountryInfo->setData('name_default', 'United Arab Emirates');
        $testCountryInfo->setData('name_en_US', 'United Arab Emirates');

        $countryCollection = $this->createMock(Collection::class);
        $countryCollection->expects($this->once())->method('load')->willReturnSelf();

        $this->directoryHelper->expects($this->once())->method('getCountryCollection')->willReturn($countryCollection);
        $countryCollection->expects($this->once())->method('getItemById')->willReturn(null);
        $this->model->getCountryInfo('AE');
    }
}
