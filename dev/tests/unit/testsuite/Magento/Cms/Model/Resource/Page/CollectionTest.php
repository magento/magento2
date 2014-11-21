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
namespace Magento\Cms\Model\Resource\Page;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\QueryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Data\SearchResultIteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultIteratorFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultProcessorMock;

    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection
     */
    protected $collection;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->queryMock = $this->getMockForAbstractClass(
            'Magento\Framework\DB\QueryInterface',
            [],
            '',
            false,
            true,
            true,
            ['fetchAll', 'getIdFieldName', 'getConnection', 'getResource']
        );
        $this->entityFactoryMock = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\EntityFactoryInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->resultIteratorFactoryMock = $this->getMock(
            'Magento\Framework\Data\SearchResultIteratorFactory',
            [],
            [],
            '',
            false
        );
        $this->searchResultProcessorMock = $this->getMock(
            'Magento\Framework\Data\SearchResultProcessor',
            [],
            [],
            '',
            false
        );
        $searchResultProcessorFactoryMock = $this->getMock(
            'Magento\Framework\Data\SearchResultProcessorFactory',
            [],
            [],
            '',
            false
        );
        $searchResultProcessorFactoryMock->expects($this->any())
            ->method('create')
            ->withAnyParameters()
            ->willReturn($this->searchResultProcessorMock);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\StoreManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getStore']
        );

        $this->collection = $objectManager->getObject(
            'Magento\Cms\Model\Resource\Page\Collection',
            [
                'query' => $this->queryMock,
                'entityFactory' => $this->entityFactoryMock,
                'eventManager' => $this->eventManagerMock,
                'resultIteratorFactory' => $this->resultIteratorFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'searchResultProcessorFactory' => $searchResultProcessorFactoryMock
            ]
        );
    }

    /**
     * Run test toOptionIdArray method
     *
     * @return void
     */
    public function testToOptionIdArray()
    {
        $itemsByPageId = array_fill(0, 4, 123);
        $data = [
            'item1' => ['test' => 'test'],
            'item2' => ['test' => 'test'],
            'item3' => ['test' => 'test'],
            'item4' => ['test' => 'test'],
        ];

        $objectMock = $this->getMock(
            'Magento\Framework\Object',
            ['getData', 'getPageId', 'setData', 'getTitle', 'getIdentifier'],
            [],
            '',
            false
        );
        $criteriaMock = $this->getMockForAbstractClass(
            'Magento\Framework\Api\CriteriaInterface',
            [],
            '',
            false,
            true,
            true,
            ['getPart']
        );
        $connectionMock = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            false,
            true,
            true,
            ['fetchPairs', 'select']
        );
        $resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['getTable']
        );
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['from', 'where'],
            [],
            '',
            false
        );
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getCode'],
            [],
            '',
            false
        );

        $this->queryMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($data));

        $this->searchResultProcessorMock->expects($this->once())
            ->method('getColumnValues')
            ->with('page_id')
            ->will($this->returnValue($itemsByPageId));
        $this->queryMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id_field_name'));
        $objectMock->expects($this->any())
            ->method('getData')
            ->will(
                $this->returnValueMap(
                    [
                        ['id_field_name', null, null],
                        ['page_id', null, 123]
                    ]
                )
            );
        $this->entityFactoryMock->expects($this->any())
            ->method('create')
            ->with('Magento\Cms\Api\Data\PageInterface', ['data' => ['test' => 'test']])
            ->will($this->returnValue($objectMock));
        $this->queryMock->expects($this->once())
            ->method('getCriteria')
            ->will($this->returnValue($criteriaMock));
        $criteriaMock->expects($this->once())
            ->method('getPart')
            ->with('first_store_flag')
            ->will($this->returnValue(true));
        $this->queryMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));
        $this->queryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));
        $connectionMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->once())
            ->method('from')
            ->with(['cps' => 'query_table'])
            ->will($this->returnSelf());
        $resourceMock->expects($this->once())
            ->method('getTable')
            ->with('cms_page_store')
            ->will($this->returnValue('query_table'));
        $selectMock->expects($this->once())
            ->method('where')
            ->with('cps.page_id IN (?)', array_fill(0, 4, 123))
            ->will($this->returnSelf());
        $connectionMock->expects($this->once())
            ->method('fetchPairs')
            ->with($selectMock)
            ->will($this->returnValue([123 => 999]));
        $objectMock->expects($this->any())
            ->method('getPageId')
            ->will($this->returnValue(123));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(999)
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('store_code'));
        $objectMock->expects($this->any())
            ->method('setData');
        $objectMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('item-value'));
        $objectMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('identifier-value'));

        $expected = [
            [
                'value' => 'identifier-value',
                'label' => 'item-value'
            ],
            [
                'value' => 'identifier-value|123',
                'label' => 'item-value'
            ],
            [
                'value' => 'identifier-value|123',
                'label' => 'item-value'
            ],
            [
                'value' => 'identifier-value|123',
                'label' => 'item-value'
            ]
        ];
        $this->assertEquals($expected, $this->collection->toOptionIdArray());
    }
}
