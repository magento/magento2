<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Directory\Model\Config\Source;

class CountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_model;

    /**
     * @var \Magento\Directory\Model\Resource\Country\Collection
     */
    protected $_collectionMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_collectionMock = $this->getMock(
            'Magento\Directory\Model\Resource\Country\Collection',
            [],
            [],
            '',
            false
        );
        $arguments = ['countryCollection' => $this->_collectionMock];
        $this->_model = $objectManagerHelper->getObject('Magento\Directory\Model\Config\Source\Country', $arguments);
    }

    /**
     * @dataProvider toOptionArrayDataProvider
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @param array $expectedResult
     */
    public function testToOptionArray($isMultiselect, $foregroundCountries, $expectedResult)
    {
        $this->_collectionMock->expects($this->once())->method('loadData')->will($this->returnSelf());
        $this->_collectionMock->expects(
            $this->once()
        )->method(
            'setForegroundCountries'
        )->with(
            $foregroundCountries
        )->will(
            $this->returnSelf()
        );
        $this->_collectionMock->expects($this->once())->method('toOptionArray')->will($this->returnValue([]));
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
