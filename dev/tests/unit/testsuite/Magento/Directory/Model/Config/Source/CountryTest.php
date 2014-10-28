<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array(),
            array(),
            '',
            false
        );
        $arguments = array('countryCollection' => $this->_collectionMock);
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
        $this->_collectionMock->expects($this->once())->method('toOptionArray')->will($this->returnValue(array()));
        $this->assertEquals($this->_model->toOptionArray($isMultiselect, $foregroundCountries), $expectedResult);
    }

    /**
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        return array(
            array(true, 'US', array()),
            array(false, 'US', array(array('value' => '', 'label' => __('--Please Select--')))),
            array(true, '', array()),
            array(false, '', array(array('value' => '', 'label' => __('--Please Select--')))),
            array(true, array('US', 'CA'), array()),
            array(false, array('US', 'CA'), array(array('value' => '', 'label' => __('--Please Select--'))))
        );
    }
}
