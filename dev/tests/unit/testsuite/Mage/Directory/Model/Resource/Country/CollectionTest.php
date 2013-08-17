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

class Mage_Directory_Model_Resource_Country_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Directory_Model_Resource_Country_Collection
     */
    protected $_model;

    protected function setUp()
    {
        $connection = $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false);
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $connection->expects($this->once())
            ->method('select')
            ->will($this->returnValue($select));

        $resource = $this->getMockForAbstractClass('Mage_Core_Model_Resource_Db_Abstract', array(), '', false, true,
            true, array('getReadConnection', 'getMainTable', 'getTable'));
        $resource->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($connection));
        $resource->expects($this->any())
            ->method('getTable')
            ->will($this->returnArgument(0));

        $helperMock = $this->getMock('Mage_Core_Helper_String', array(), array(), '', false);
        $localeMock = $this->getMock('Mage_Core_Model_LocaleInterface');
        $localeMock->expects($this->any())->method('getCountryTranslation')->will($this->returnArgument(0));

        $fetchStrategy = $this->getMockForAbstractClass('Varien_Data_Collection_Db_FetchStrategyInterface');
        $this->_model = $this->getMock('Mage_Directory_Model_Resource_Country_Collection',
            array('_toOptionArray'), array($helperMock, $localeMock, $fetchStrategy, $resource), '', true
        );
    }

    /**
     * @dataProvider toOptionArrayDataProvider
     * @param array $optionsArray
     * @param string|boolean $emptyLabel
     * @param string|array $foregroundCountries
     * @param array $expectedResults
     */
    public function testToOptionArray($optionsArray, $emptyLabel, $foregroundCountries, $expectedResults)
    {
        $this->_model->expects($this->any())
            ->method('_toOptionArray')
            ->will($this->returnValue($optionsArray));

        $this->_model->setForegroundCountries($foregroundCountries);
        $result = $this->_model->toOptionArray($emptyLabel);
        $this->assertEquals(count($optionsArray) + (int)!empty($emptyLabel), count($result));
        foreach ($expectedResults as $index => $expectedResult) {
            $this->assertEquals($expectedResult, $result[$index]['label']);
        }
    }

    /**
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        $optionsArray = array(
            array('title' => 'AD', 'value' => 'AD', 'label' => ''),
            array('title' => 'US', 'value' => 'US', 'label' => ''),
            array('title' => 'ES', 'value' => 'ES', 'label' => ''),
            array('title' => 'BZ', 'value' => 'BZ', 'label' => ''),
        );
        return array(
            array($optionsArray, false, array(), array('AD', 'US', 'ES', 'BZ')),
            array($optionsArray, false, 'US', array('US', 'AD', 'ES', 'BZ')),
            array($optionsArray, false, array('US', 'BZ'), array('US', 'BZ', 'AD', 'ES')),
            array($optionsArray, ' ', 'US', array(' ', 'US', 'AD', 'ES', 'BZ')),
        );
    }
}
