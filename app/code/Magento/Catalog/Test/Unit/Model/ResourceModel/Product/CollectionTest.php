<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $entityFactory = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fetchStrategy = $this->getMockBuilder('Magento\Framework\Data\Collection\Db\FetchStrategyInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eventManager = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityFactory = $this->getMockBuilder('Magento\Eav\Model\EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceHelper = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Helper')
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory = $this->getMockBuilder('Magento\Framework\Validator\UniversalFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getId'])
            ->getMockForAbstractClass();
        $moduleManager = $this->getMockBuilder('Magento\Framework\Module\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductFlatState = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Flat\State')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productOptionFactory = $this->getMockBuilder('Magento\Catalog\Model\Product\OptionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $catalogUrl = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Url')
            ->disableOriginalConstructor()
            ->getMock();
        $localeDate = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $groupManagement = $this->getMockBuilder('Magento\Customer\Api\GroupManagementInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $entityMock = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager->expects($this->any())->method('getId')->willReturn(1);
        $storeManager->expects($this->any())->method('getStore')->willReturnSelf();
        $universalFactory->expects($this->exactly(1))->method('create')->willReturnOnConsecutiveCalls(
            $entityMock
        );
        $entityMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn([]);
        $entityMock->expects($this->any())->method('getTable')->willReturnArgument(0);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);
        $helper = new ObjectManager($this);
        $this->collection = $helper->getObject(
            'Magento\Catalog\Model\ResourceModel\Product\Collection',
            [
                'entityFactory' => $entityFactory,
                'logger' => $logger,
                'fetchStrategy' => $fetchStrategy,
                'eventManager' => $eventManager,
                'eavConfig' => $eavConfig,
                'resource' => $resource,
                'eavEntityFactory' => $eavEntityFactory,
                'resourceHelper' => $resourceHelper,
                'universalFactory' => $universalFactory,
                'storeManager' => $storeManager,
                'moduleManager' => $moduleManager,
                'catalogProductFlatState' => $catalogProductFlatState,
                'scopeConfig' => $scopeConfig,
                'productOptionFactory' => $productOptionFactory,
                'catalogUrl' => $catalogUrl,
                'localeDate' => $localeDate,
                'customerSession' => $customerSession,
                'dateTime' => $dateTime,
                'groupManagement' => $groupManagement,
                'connection' => $this->connectionMock
            ]
        );
        $this->collection->setConnection($this->connectionMock);
    }

    public function testAddProductCategoriesFilter()
    {
        $condition = ['in' => [1,2]];
        $values = [1,2];
        $conditionType = 'nin';
        $preparedSql = "category_id IN(1,2)";
        $tableName = "catalog_category_product";
        $this->connectionMock->expects($this->any())->method('getId')->willReturn(1);
        $this->connectionMock->expects($this->exactly(2))->method('prepareSqlCondition')->withConsecutive(
            ['cat.category_id', $condition],
            ['e.entity_id', [$conditionType => $this->selectMock]]
        )->willReturnOnConsecutiveCalls(
            $preparedSql,
            'e.entity_id IN (1,2)'
        );
        $this->selectMock->expects($this->once())->method('from')->with(
            ['cat' => $tableName],
            'cat.product_id'
        )->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))->method('where')->withConsecutive(
            [$preparedSql],
            ['e.entity_id IN (1,2)']
        )->willReturnSelf();
        $this->collection->addCategoriesFilter([$conditionType => $values]);
    }
}
