<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject;
use Magento\Store\Model\Address\Renderer;
use Magento\Store\Model\Information;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InformationTest extends TestCase
{
    /**
     * @var Information
     */
    protected $model;

    /**
     * @var Store|MockObject
     */
    protected $store;

    /**
     * @var Renderer|MockObject
     */
    protected $renderer;

    /**
     * @var RegionFactory|MockObject
     */
    protected $regionFactory;

    /**
     * @var CountryFactory|MockObject
     */
    protected $countryFactory;

    /**
     * @var array
     */
    protected $mockConfigData;

    /**
     * Init mocks for tests
     */
    protected function setUp(): void
    {
        $mockData = $this->mockConfigData = [
            Information::XML_PATH_STORE_INFO_NAME => 'Country Furnishings',
            Information::XML_PATH_STORE_INFO_PHONE => '000-000-0000',
            Information::XML_PATH_STORE_INFO_HOURS => '9 AM to 5 PM',
            Information::XML_PATH_STORE_INFO_STREET_LINE1 => '1234 Example Ct',
            Information::XML_PATH_STORE_INFO_STREET_LINE2 => 'Suite A',
            Information::XML_PATH_STORE_INFO_CITY => 'Aldburg',
            Information::XML_PATH_STORE_INFO_POSTCODE => '65804',
            Information::XML_PATH_STORE_INFO_REGION_CODE => 1989,
            Information::XML_PATH_STORE_INFO_COUNTRY_CODE => 'ED',
            Information::XML_PATH_STORE_INFO_VAT_NUMBER => '123456789',
        ];

        $this->store = $this->createMock(Store::class);

        $this->store->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($path) use ($mockData) {
                return $mockData[$path] ?? null;
            });

        $this->renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['format'])
            ->getMock();

        $this->renderer->expects($this->once())
            ->method('format')
            ->willReturnCallback(function ($storeInfo) {
                return implode("\n", $storeInfo->getData());
            });

        $region = $this->getMockBuilder(DataObject::class)
            ->addMethods(['load', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $region->expects($this->once())->method('load')->willReturnSelf();
        $region->expects($this->once())->method('getName')->willReturn('Rohan');

        $this->regionFactory = $this->createMock(RegionFactory::class);
        $this->regionFactory->expects($this->once())->method('create')->willReturn($region);

        $country = $this->getMockBuilder(DataObject::class)
            ->addMethods(['loadByCode', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $country->expects($this->once())->method('loadByCode')->with('ED')->willReturnSelf();
        $country->expects($this->once())->method('getName')->willReturn('Edoras');

        $this->countryFactory = $this->createMock(CountryFactory::class);
        $this->countryFactory->expects($this->once())->method('create')->willReturn($country);

        $this->model = new Information(
            $this->renderer,
            $this->regionFactory,
            $this->countryFactory
        );
    }

    /**
     * @covers \Magento\Store\Model\Information::getFormattedAddress
     * @covers \Magento\Store\Model\Information::getStoreInformationObject
     */
    public function testGetFormattedAddress()
    {
        $expected = implode("\n", $this->mockConfigData + ['country' => 'Rohan', 'region' => 'Edoras']);
        $result = $this->model->getFormattedAddress($this->store);
        $this->assertEquals($expected, $result);
    }
}
