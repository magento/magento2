<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Config\Source;

use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    /**
     * @var Country
     */
    protected $_model;

    /**
     * @var Collection
     */
    protected $_collectionMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_collectionMock = $this->createMock(Collection::class);
        $arguments = ['countryCollection' => $this->_collectionMock];
        $this->_model = $objectManagerHelper->getObject(
            Country::class,
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
