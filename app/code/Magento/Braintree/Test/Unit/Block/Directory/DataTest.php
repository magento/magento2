<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block\Directory;

class DataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCountryCollection()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $countrySourceMock = $this->getMockBuilder('\Magento\Braintree\Model\System\Config\Source\Country')
            ->disableOriginalConstructor()
            ->setMethods(['getRestrictedCountries'])
            ->getMock();

        $countryCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Directory\Model\ResourceModel\Country\CollectionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $countryCollectionMock = $this->getMockBuilder('\Magento\Directory\Model\ResourceModel\Country\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'loadByStore'])
            ->getMock();

        $countryCollectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $country = $objectManagerHelper->getObject('Magento\Directory\Model\Country');
        $country->setData('country_id', 'US');

        $countryCollectionMock->expects($this->any())
            ->method('loadByStore')
            ->willReturn([$country]);

        $countryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($countryCollectionMock);

        $configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->setMethods(['canUseForCountry'])
            ->getMock();

        $configMock->expects($this->once())
            ->method('canUseForCountry')
            ->with('US')
            ->willReturn(true);

        $data = $objectManagerHelper->getObject(
            'Magento\Braintree\Block\Directory\Data',
            [
                'countrySource' => $countrySourceMock,
                'countryCollectionFactory' => $countryCollectionFactoryMock,
                'config' => $configMock
            ]
        );

        $result = $data->getCountryCollection();
        $this->assertSame($result[0]->getData('country_id'), $country->getData('country_id'));
    }
}
