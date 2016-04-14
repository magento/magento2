<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Directory\Test\Unit\Model\ResourceModel\Country;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $connection = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));

        $resource = $this->getMockForAbstractClass('Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $localeListsMock = $this->getMock('Magento\Framework\Locale\ListsInterface');
        $localeListsMock->expects($this->any())->method('getCountryTranslation')->will($this->returnArgument(0));

        $fetchStrategy = $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $entityFactory = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);
        $scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $countryFactory = $this->getMock(
            'Magento\Directory\Model\ResourceModel\CountryFactory',
            [],
            [],
            '',
            false
        );
        $helperDataMock = $this->getMock(
            'Magento\Directory\Helper\Data',
            [],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'logger' => $logger,
            'eventManager' => $eventManager,
            'localeLists' => $localeListsMock,
            'fetchStrategy' => $fetchStrategy,
            'entityFactory' => $entityFactory,
            'scopeConfig' => $scopeConfigMock,
            'countryFactory' => $countryFactory,
            'resource' => $resource,
            'helperData' => $helperDataMock
        ];
        $this->_model = $objectManager->getObject('Magento\Directory\Model\ResourceModel\Country\Collection', $arguments);
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
            $this->_model->addItem(new \Magento\Framework\DataObject($itemData));
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
        $optionsArray = [
            ['iso2_code' => 'AD', 'country_id' => 'AD', 'name' => ''],
            ['iso2_code' => 'US', 'country_id' => 'US', 'name' => ''],
            ['iso2_code' => 'ES', 'country_id' => 'ES', 'name' => ''],
            ['iso2_code' => 'BZ', 'country_id' => 'BZ', 'name' => ''],
        ];
        return [
            [$optionsArray, false, [], ['AD', 'US', 'ES', 'BZ']],
            [$optionsArray, false, 'US', ['US', 'AD', 'ES', 'BZ']],
            [$optionsArray, false, ['US', 'BZ'], ['US', 'BZ', 'AD', 'ES']],
            [$optionsArray, ' ', 'US', [' ', 'US', 'AD', 'ES', 'BZ']]
        ];
    }
}
