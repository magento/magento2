<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Config\Source;

class CountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_model;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_collectionMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_collectionMock = $this->createMock(\Magento\Directory\Model\ResourceModel\Country\Collection::class);
        $arguments = ['countryCollection' => $this->_collectionMock];
        $this->_model = $objectManagerHelper->getObject(
            \Magento\Directory\Model\Config\Source\Country::class,
            $arguments
        );
    }

    /**
     * @dataProvider toOptionArrayDataProvider
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @param array $expectedResult
     */
    public function testToOptionArray($isMultiselect, $foregroundCountries, $expectedResult)
    {
        $this->_collectionMock->expects($this->once())->method('loadData')->willReturnSelf();
        $this->_collectionMock->expects(
            $this->once()
        )->method(
            'setForegroundCountries'
        )->with(
            $foregroundCountries
        )->willReturnSelf(
            
        );
        $this->_collectionMock->expects($this->once())->method('toOptionArray')->willReturn([]);
        $this->assertEquals($this->_model->toOptionArray($isMultiselect, $foregroundCountries), $expectedResult);
    }

    /**
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        return [
            [true, 'US', []],
            [false, 'US', [['value' => '', 'label' => __('--Please Select--')]]],
            [true, '', []],
            [false, '', [['value' => '', 'label' => __('--Please Select--')]]],
            [true, ['US', 'CA'], []],
            [false, ['US', 'CA'], [['value' => '', 'label' => __('--Please Select--')]]]
        ];
    }
}
