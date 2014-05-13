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
namespace Magento\Directory\Model\Resource\Country;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Resource\Country\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $connection = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));

        $resource = $this->getMockForAbstractClass('Magento\Framework\Model\Resource\Db\AbstractDb',
            array(),
            '',
            false,
            true,
            true,
            array('getReadConnection', 'getMainTable', 'getTable', '__wakeup')
        );
        $resource->expects($this->any())->method('getReadConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $localeListsMock = $this->getMock('Magento\Framework\Locale\ListsInterface');
        $localeListsMock->expects($this->any())->method('getCountryTranslation')->will($this->returnArgument(0));

        $fetchStrategy = $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $countryFactory = $this->getMock(
            'Magento\Directory\Model\Resource\CountryFactory',
            array(),
            array(),
            '',
            false
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'logger' => $logger,
            'eventManager' => $eventManager,
            'localeLists' => $localeListsMock,
            'fetchStrategy' => $fetchStrategy,
            'entityFactory' => $entityFactory,
            'scopeConfig' => $scopeConfigMock,
            'countryFactory' => $countryFactory,
            'resource' => $resource
        );
        $this->_model = $objectManager->getObject('Magento\Directory\Model\Resource\Country\Collection', $arguments);
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
        foreach ($optionsArray as $itemData) {
            $this->_model->addItem(new \Magento\Framework\Object($itemData));
        }

        $this->_model->setForegroundCountries($foregroundCountries);
        $result = $this->_model->toOptionArray($emptyLabel);
        $this->assertEquals(count($optionsArray) + (int)(!empty($emptyLabel)), count($result));
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
            array('iso2_code' => 'AD', 'country_id' => 'AD', 'name' => ''),
            array('iso2_code' => 'US', 'country_id' => 'US', 'name' => ''),
            array('iso2_code' => 'ES', 'country_id' => 'ES', 'name' => ''),
            array('iso2_code' => 'BZ', 'country_id' => 'BZ', 'name' => '')
        );
        return array(
            array($optionsArray, false, array(), array('AD', 'US', 'ES', 'BZ')),
            array($optionsArray, false, 'US', array('US', 'AD', 'ES', 'BZ')),
            array($optionsArray, false, array('US', 'BZ'), array('US', 'BZ', 'AD', 'ES')),
            array($optionsArray, ' ', 'US', array(' ', 'US', 'AD', 'ES', 'BZ'))
        );
    }
}
