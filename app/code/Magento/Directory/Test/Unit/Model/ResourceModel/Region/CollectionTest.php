<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\ResourceModel\Region;

use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
    private $collection;

    /**
     * @var MockObject
     */
    private $allowedCountries;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $entityFactoryMock = $this->createMock(EntityFactory::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $fetchStrategyMock = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $connectionMock = $this->createMock(Mysql::class);
        $resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $this->allowedCountries = $this->createMock(AllowedCountries::class);

        $selectMock = $this->createMock(Select::class);
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $resourceMock->expects($this->any())->method('getTable')->willReturnArgument(0);

        $this->collection = new Collection(
            $entityFactoryMock,
            $loggerMock,
            $fetchStrategyMock,
            $eventManagerMock,
            $localeResolverMock,
            $connectionMock,
            $resourceMock
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->collection,
            'allowedCountriesReader',
            $this->allowedCountries
        );
    }

    public function testToOptionArray()
    {
        $items = [
            [
                'name' => 'Region Name 1',
                'default_name' => 'Default Region Name 1',
                'region_id' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Region Name 2',
                'default_name' => 'Default Region Name 2',
                'region_id' => 2,
                'country_id' => 1,
            ],
        ];
        foreach ($items as $itemData) {
            $this->collection->addItem(new DataObject($itemData));
        }

        $expectedResult = [
            [
                'label' => __('Please select a region, state or province.'),
                'value' => null,
                'title' => null,
            ],
            [
                'value' => 1,
                'title' => 'Default Region Name 1',
                'country_id' => 1,
                'label' => 'Region Name 1',
            ],
            [
                'value' => 2,
                'title' => 'Default Region Name 2',
                'country_id' => 1,
                'label' => 'Region Name 2',
            ],
        ];

        $this->assertEquals($expectedResult, $this->collection->toOptionArray());
    }

    public function testAddAllowedCountriesFilter()
    {
        $allowedCountries = [1, 2, 3];
        $this->allowedCountries->expects($this->once())->method('getAllowedCountries')->with(
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn($allowedCountries);
        $this->assertEquals($this->collection->addAllowedCountriesFilter(), $this->collection);
    }
}
