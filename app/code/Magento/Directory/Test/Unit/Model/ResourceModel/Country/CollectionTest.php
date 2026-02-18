<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\ResourceModel\Country;

use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $_model;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $connection = $this->createMock(Mysql::class);
        $select = $this->createMock(Select::class);
        $connection->expects($this->once())->method('select')->willReturn($select);

        $resource = $this->createMock(AbstractDb::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($connection);
        $resource->expects($this->any())->method('getTable')->willReturnArgument(0);

        $eventManager = $this->createMock(ManagerInterface::class);
        $localeListsMock = $this->createMock(ListsInterface::class);
        $localeListsMock->expects($this->any())->method('getCountryTranslation')->willReturnArgument(0);

        $fetchStrategy = $this->createMock(FetchStrategyInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $countryFactory = $this->createMock(CountryFactory::class);
        $helperDataMock = $this->createMock(Data::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $objectManager = new ObjectManager($this);
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
            ->getObject(Collection::class, $arguments);
    }

    /**
     * @param array $optionsArray
     * @param string|boolean $emptyLabel
     * @param string|array $foregroundCountries
     * @param array $expectedResults
     */
    #[DataProvider('toOptionArrayDataProvider')]
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
            $this->_model->addItem(new DataObject($itemData));
        }

        $this->_model->setForegroundCountries($foregroundCountries);
        $result = $this->_model->toOptionArray($emptyLabel);
        $this->assertCount(count($optionsArray) + (int)(!empty($emptyLabel)), $result);
        foreach ($expectedResults as $index => $expectedResult) {
            $this->assertEquals($expectedResult, $result[$index]['label']);
        }
    }

    /**
     * @return array
     */
    public static function toOptionArrayDataProvider()
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
            [$optionsArray, ' ', 'US', [' ', 'US', 'AD', 'ES', 'BZ']],
            [$optionsArray, ' ', [], [' ', 'AD', 'US', 'ES', 'BZ']],
            [$optionsArray, ' ', 'UA', [' ', 'AD', 'US', 'ES', 'BZ']],
            [$optionsArray, ' ', ['AF', 'UA'], [' ', 'AD', 'US', 'ES', 'BZ']],
        ];
    }
}
