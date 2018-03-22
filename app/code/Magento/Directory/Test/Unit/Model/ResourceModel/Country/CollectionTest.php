<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Directory\Test\Unit\Model\ResourceModel\Country;

use Magento\Store\Api\Data\WebsiteInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));

        $resource = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $localeListsMock = $this->createMock(\Magento\Framework\Locale\ListsInterface::class);
        $localeListsMock->expects($this->any())->method('getCountryTranslation')->will($this->returnArgument(0));

        $fetchStrategy = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );
        $entityFactory = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $countryFactory = $this->createMock(\Magento\Directory\Model\ResourceModel\CountryFactory::class);
        $helperDataMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'logger' => $logger,
            'eventManager' => $eventManager,
            'localeLists' => $localeListsMock,
            'fetchStrategy' => $fetchStrategy,
            'entityFactory' => $entityFactory,
            'scopeConfig' => $this->scopeConfigMock,
            'countryFactory' => $countryFactory,
            'resource' => $resource,
            'helperData' => $helperDataMock,
            'storeManager' => $this->storeManagerMock
        ];
        $this->_model = $objectManager
            ->getObject(\Magento\Directory\Model\ResourceModel\Country\Collection::class, $arguments);
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
        $website1 = $this->createMock(WebsiteInterface::class);
        $website1->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$website1]);

        foreach ($optionsArray as $itemData) {
            $this->_model->addItem(new \Magento\Framework\DataObject($itemData));
        }

        $this->_model->setForegroundCountries($foregroundCountries);
        $result = $this->_model->toOptionArray($emptyLabel);
        $this->assertCount(count($optionsArray) + (int)(!empty($emptyLabel) && !empty($foregroundCountries)) +
            (int)(!empty($emptyLabel)), $result);
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
            [$optionsArray, ' ', 'US', [' ', 'US', ' ', 'AD', 'ES', 'BZ']],
            [$optionsArray, ' ', [], [' ', 'AD', 'US', 'ES', 'BZ']]
        ];
    }
}
