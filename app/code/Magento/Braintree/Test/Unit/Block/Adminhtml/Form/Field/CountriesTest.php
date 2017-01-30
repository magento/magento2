<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block\Adminhtml\Form\Field;

class CountriesTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtml()
    {
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
            ->setMethods(['addFieldToFilter', 'loadData', 'toOptionArray'])
            ->getMock();

        $countryCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $countryCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();

        $countryCollectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'US', 'label' => 'US']]);

        $countryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($countryCollectionMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $countries = $objectManagerHelper->getObject(
            'Magento\Braintree\Block\Adminhtml\Form\Field\Countries',
            [
                '$countrySource' => $countrySourceMock,
                'countryCollectionFactory' => $countryCollectionFactoryMock
            ]
        );

        $result = $countries->_toHtml();
        $this->assertSame($result, '<select name="" id="" class="" title="" ><option value="" ></option></select>');
    }
}
