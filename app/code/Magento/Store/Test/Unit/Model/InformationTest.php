<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Store\Model\Information;

class InformationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Information
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $store;

    /**
     * @var \Magento\Store\Model\Address\Renderer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $renderer;

    /**
     * @var \Magento\Directory\Model\RegionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory|\PHPUnit\Framework\MockObject\MockObject
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

        $this->store = $this->createMock(\Magento\Store\Model\Store::class);

        $this->store->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($path) use ($mockData) {
                return isset($mockData[$path]) ? $mockData[$path] : null;
            });

        $this->renderer = $this->getMockBuilder(\Magento\Store\Model\Address\Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['format'])
            ->getMock();

        $this->renderer->expects($this->once())
            ->method('format')
            ->willReturnCallback(function ($storeInfo) {
                return implode("\n", $storeInfo->getData());
            });

        $region = $this->createPartialMock(\Magento\Framework\DataObject::class, ['load', 'getName']);
        $region->expects($this->once())->method('load')->willReturnSelf();
        $region->expects($this->once())->method('getName')->willReturn('Rohan');

        $this->regionFactory = $this->createMock(\Magento\Directory\Model\RegionFactory::class);
        $this->regionFactory->expects($this->once())->method('create')->willReturn($region);

        $country = $this->createPartialMock(\Magento\Framework\DataObject::class, ['loadByCode', 'getName']);
        $country->expects($this->once())->method('loadByCode')->with('ED')->willReturnSelf();
        $country->expects($this->once())->method('getName')->willReturn('Edoras');

        $this->countryFactory = $this->createMock(\Magento\Directory\Model\CountryFactory::class);
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
