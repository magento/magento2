<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\System\Config\Source;

use Magento\Braintree\Model\Config;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CountryTest
 *
 */
class CountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country
     */
    protected $model;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryCollectionMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->countryCollectionMock = $this->getMockBuilder(
            '\Magento\Directory\Model\ResourceModel\Country\Collection'
        )->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Model\System\Config\Source\Country',
            [
                'countryCollection' => $this->countryCollectionMock,
            ]
        );
    }

    public function testToOptionArrayMultiSelect()
    {
        $excludedCountries = $this->model->getRestrictedCountries();
        $countries = [
            [
                'value' => 'US',
                'lable' => 'United States',
            ],
            [
                'value' => 'countryCode',
                'lable' => 'countryName',
            ],
        ];

        $this->countryCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('country_id', ['nin' => $excludedCountries])
            ->willReturnSelf();
        $this->countryCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();
        $this->countryCollectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($countries);

        $this->assertEquals($countries, $this->model->toOptionArray(true));
    }

    public function testToOptionArray()
    {
        $excludedCountries = $this->model->getRestrictedCountries();
        $countries = [
            [
                'value' => 'US',
                'lable' => 'United States',
            ],
            [
                'value' => 'countryCode',
                'lable' => 'countryName',
            ],
        ];

        $this->countryCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('country_id', ['nin' => $excludedCountries])
            ->willReturnSelf();
        $this->countryCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();
        $this->countryCollectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($countries);

        $header = ['value'=>'', 'label'=> new \Magento\Framework\Phrase('--Please Select--')];
        array_unshift($countries, $header);

        $this->assertEquals($countries, $this->model->toOptionArray());
    }
}
