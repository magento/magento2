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
 * @category    Magento
 * @package     Mage_Directory
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Directory_Model_Config_Source_CountryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Directory_Model_Config_Source_Country
     */
    protected $_model;

    /**
     * @var Mage_Directory_Model_Resource_Country_Collection
     */
    protected $_collectionMock;

    protected function setUp()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_collectionMock = $this->getMock(
            'Mage_Directory_Model_Resource_Country_Collection', array(), array(), '', false
        );
        $arguments = array('countryCollection' => $this->_collectionMock);
        $this->_model = $objectManagerHelper->getObject('Mage_Directory_Model_Config_Source_Country', $arguments);

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
        $this->_collectionMock->expects($this->once())->method('setForegroundCountries')
            ->with($foregroundCountries)
            ->will($this->returnSelf());
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
            array(false, 'US', array(array('value' => '', 'label' => ''))),
            array(true, '', array()),
            array(false, '', array(array('value' => '', 'label' => ''))),
            array(true, array('US', 'CA'), array()),
            array(false, array('US', 'CA'), array(array('value' => '', 'label' => ''))),
        );
    }
}
