<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\ResourceModel\Region;

use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CollectionTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var MockObject
     */
    private $allowedCountries;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $entityFactoryMock = $this->getMock(EntityFactory::class, [], [], '', false);
        $loggerMock = $this->getMock(LoggerInterface::class);
        $fetchStrategyMock = $this->getMock(FetchStrategyInterface::class);
        $eventManagerMock = $this->getMock(ManagerInterface::class);
        $localeResolverMock = $this->getMock(ResolverInterface::class);
        $connectionMock = $this->getMock(Mysql::class, [], [], '', false);
        $resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $this->allowedCountries = $this->getMock(AllowedCountries::class, [], [], '', false);

        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $resourceMock->expects($this->any())->method('getTable')->will($this->returnArgument(0));

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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        )->willReturn($allowedCountries);
        $this->assertEquals($this->collection->addAllowedCountriesFilter(), $this->collection);
    }
}
