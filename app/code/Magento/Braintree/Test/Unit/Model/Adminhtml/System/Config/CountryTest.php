<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Adminhtml\System\Config;

use Magento\Braintree\Model\Adminhtml\System\Config\Country;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CountryTest
 *
 */
class CountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Braintree\Model\Adminhtml\System\Config\Country
     */
    protected $model;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countryCollectionMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->countryCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            Country::class,
            [
                'countryCollection' => $this->countryCollectionMock,
            ]
        );
    }

    /**
     * @covers \Magento\Braintree\Model\Adminhtml\System\Config\Country::toOptionArray
     */
    public function testToOptionArrayMultiSelect()
    {
        $countries = [
            [
                'value' => 'US',
                'label' => 'United States',
            ],
            [
                'value' => 'UK',
                'label' => 'United Kingdom',
            ],
        ];
        $this->initCountryCollectionMock($countries);

        $this->assertEquals($countries, $this->model->toOptionArray(true));
    }

    /**
     * @covers \Magento\Braintree\Model\Adminhtml\System\Config\Country::toOptionArray
     */
    public function testToOptionArray()
    {
        $countries = [
            [
                'value' => 'US',
                'label' => 'United States',
            ],
            [
                'value' => 'UK',
                'label' => 'United Kingdom',
            ],
        ];
        $this->initCountryCollectionMock($countries);

        $header = ['value' => '', 'label' => new Phrase('--Please Select--')];
        array_unshift($countries, $header);

        $this->assertEquals($countries, $this->model->toOptionArray());
    }

    /**
     * @covers \Magento\Braintree\Model\Adminhtml\System\Config\Country::isCountryRestricted
     * @param string $countryId
     * @dataProvider countryDataProvider
     */
    public function testIsCountryRestricted($countryId)
    {
        static::assertTrue($this->model->isCountryRestricted($countryId));
    }

    /**
     * Get simple list of not available braintree countries
     * @return array
     */
    public function countryDataProvider()
    {
        return [
            ['MM'],
            ['IR'],
            ['SD'],
            ['BY'],
            ['CI'],
            ['CD'],
            ['CG'],
            ['IQ'],
            ['LR'],
            ['LB'],
            ['KP'],
            ['SL'],
            ['SY'],
            ['ZW'],
            ['AL'],
            ['BA'],
            ['MK'],
            ['ME'],
            ['RS']
        ];
    }

    /**
     * Init country collection mock
     * @param array $countries
     */
    protected function initCountryCollectionMock(array $countries)
    {
        $this->countryCollectionMock->expects(static::once())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->countryCollectionMock->expects(static::once())
            ->method('loadData')
            ->willReturnSelf();
        $this->countryCollectionMock->expects(static::once())
            ->method('toOptionArray')
            ->willReturn($countries);
    }
}
