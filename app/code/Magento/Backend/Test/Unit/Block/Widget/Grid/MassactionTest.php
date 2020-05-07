<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Massaction;
use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface as VisibilityChecker;
use Magento\Backend\Model\Url;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Authorization;
use Magento\Framework\Data\Collection\AbstractDb as Collection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Backend\Block\Widget\Grid\Massaction
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassactionTest extends TestCase
{
    /**
     * @var Massaction
     */
    protected $_block;

    /**
     * @var Layout|MockObject
     */
    protected $_layoutMock;

    /**
     * @var Grid|MockObject
     */
    protected $_gridMock;

    /**
     * @var MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var Url|MockObject
     */
    protected $_urlModelMock;

    /**
     * @var Http|MockObject
     */
    protected $_requestMock;

    /**
     * @var Authorization|MockObject
     */
    protected $_authorizationMock;

    /**
     * @var VisibilityChecker|MockObject
     */
    private $visibilityCheckerMock;

    /**
     * @var Collection|MockObject
     */
    private $gridCollectionMock;

    /**
     * @var Select|MockObject
     */
    private $gridCollectionSelectMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $this->_gridMock = $this->getMockBuilder(Grid::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['getId', 'getCollection'])
            ->getMock();
        $this->_gridMock->expects($this->any())
            ->method('getId')
            ->willReturn('test_grid');

        $this->_layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['getParentName', 'getBlock', 'helper'])
            ->getMock();
        $this->_layoutMock->expects($this->any())
            ->method('getParentName')
            ->with('test_grid_massaction')
            ->willReturn('test_grid');
        $this->_layoutMock->expects($this->any())
            ->method('getBlock')
            ->with('test_grid')
            ->willReturn($this->_gridMock);

        $this->_requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->_urlModelMock = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->visibilityCheckerMock = $this->getMockBuilder(VisibilityChecker::class)
            ->getMockForAbstractClass();

        $this->_authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();

        $this->gridCollectionMock = $this->createMock(Collection::class);
        $this->gridCollectionSelectMock = $this->createMock(Select::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->gridCollectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->gridCollectionSelectMock);

        $this->gridCollectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $arguments = [
            'layout' => $this->_layoutMock,
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlModelMock,
            'data' => ['massaction_id_field' => 'test_id', 'massaction_id_filter' => 'test_id'],
            'authorization' => $this->_authorizationMock,
        ];

        $objectManagerHelper = new ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(
            Massaction::class,
            $arguments
        );
        $this->_block->setNameInLayout('test_grid_massaction');
    }

    protected function tearDown(): void
    {
        unset($this->_layoutMock);
        unset($this->_eventManagerMock);
        unset($this->_gridMock);
        unset($this->_urlModelMock);
        unset($this->_block);
    }

    public function testMassactionDefaultValues()
    {
        $this->assertEquals(0, $this->_block->getCount());
        $this->assertFalse($this->_block->isAvailable());

        $this->assertEquals('massaction', $this->_block->getFormFieldName());
        $this->assertEquals('internal_massaction', $this->_block->getFormFieldNameInternal());

        $this->assertEquals('test_grid_massactionJsObject', $this->_block->getJsObjectName());
        $this->assertEquals('test_gridJsObject', $this->_block->getGridJsObjectName());

        $this->assertEquals('test_grid_massaction', $this->_block->getHtmlId());
        $this->assertTrue($this->_block->getUseSelectAll());
    }

    /**
     * @param string $itemId
     * @param DataObject $item
     * @param $expectedItem \Magento\Framework\DataObject
     * @dataProvider itemsProcessingDataProvider
     */
    public function testItemsProcessing($itemId, $item, $expectedItem)
    {
        $this->_urlModelMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('http://localhost/index.php');

        $urlReturnValueMap = [
            ['*/*/test1', [], 'http://localhost/index.php/backend/admin/test/test1'],
            ['*/*/test2', [], 'http://localhost/index.php/backend/admin/test/test2'],
        ];
        $this->_urlModelMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap($urlReturnValueMap);

        $this->_authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);

        $this->_block->addItem($itemId, $item);
        $this->assertEquals(1, $this->_block->getCount());

        $actualItem = $this->_block->getItem($itemId);
        $this->assertInstanceOf(DataObject::class, $actualItem);
        $this->assertEquals($expectedItem->getData(), $actualItem->getData());

        $this->_block->removeItem($itemId);
        $this->assertEquals(0, $this->_block->getCount());
        $this->assertNull($this->_block->getItem($itemId));
    }

    /**
     * @return array
     */
    public function itemsProcessingDataProvider()
    {
        return [
            [
                'test_id1',
                ["label" => "Test Item One", "url" => "*/*/test1"],
                new DataObject(
                    [
                        "label" => "Test Item One",
                        "url" => "http://localhost/index.php/backend/admin/test/test1",
                        "id" => 'test_id1',
                    ]
                ),
            ],
            [
                'test_id2',
                new DataObject(["label" => "Test Item Two", "url" => "*/*/test2"]),
                new DataObject(
                    [
                        "label" => "Test Item Two",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'test_id2',
                    ]
                )
            ],
            [
                'enabled',
                new DataObject(["label" => "Test Item Enabled", "url" => "*/*/test2"]),
                new DataObject(
                    [
                        "label" => "Test Item Enabled",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'enabled',
                    ]
                )
            ],
            [
                'refresh',
                new DataObject(["label" => "Test Item Refresh", "url" => "*/*/test2"]),
                new DataObject(
                    [
                        "label" => "Test Item Refresh",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'refresh',
                    ]
                )
            ]
        ];
    }

    /**
     * @param string $param
     * @param string $expectedJson
     * @param array $expected
     * @dataProvider selectedDataProvider
     */
    public function testSelected($param, $expectedJson, $expected)
    {
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->_block->getFormFieldNameInternal())
            ->willReturn($param);

        $this->assertEquals($expectedJson, $this->_block->getSelectedJson());
        $this->assertEquals($expected, $this->_block->getSelected());
    }

    /**
     * @return array
     */
    public function selectedDataProvider()
    {
        return [
            ['', '', []],
            ['test_id1,test_id2', 'test_id1,test_id2', ['test_id1', 'test_id2']]
        ];
    }

    public function testUseSelectAll()
    {
        $this->_block->setUseSelectAll(false);
        $this->assertFalse($this->_block->getUseSelectAll());

        $this->_block->setUseSelectAll(true);
        $this->assertTrue($this->_block->getUseSelectAll());
    }

    public function testGetGridIdsJsonWithoutUseSelectAll()
    {
        $this->_block->setUseSelectAll(false);
        $this->assertEmpty($this->_block->getGridIdsJson());
    }

    /**
     * Test for getGridIdsJson when select all functionality flag set to true.
     */
    public function testGetGridIdsJsonWithUseSelectAll()
    {
        $this->_block->setUseSelectAll(true);

        $this->_gridMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->gridCollectionMock);

        $this->gridCollectionSelectMock->expects($this->exactly(4))
            ->method('reset')
            ->withConsecutive(
                [Select::ORDER],
                [Select::LIMIT_COUNT],
                [Select::LIMIT_OFFSET],
                [Select::COLUMNS]
            );

        $this->gridCollectionSelectMock->expects($this->once())
            ->method('columns')
            ->with('test_id');

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->gridCollectionSelectMock)
            ->willReturn([1, 2, 3]);

        $this->assertEquals(
            '1,2,3',
            $this->_block->getGridIdsJson()
        );
    }

    /**
     * @param string $itemId
     * @param array|DataObject $item
     * @param int $count
     * @param bool $withVisibilityChecker
     * @param bool $isVisible
     * @param bool $isAllowed
     *
     * @dataProvider addItemDataProvider
     */
    public function testAddItem($itemId, $item, $count, $withVisibilityChecker, $isVisible, $isAllowed)
    {
        $this->visibilityCheckerMock->expects($this->any())
            ->method('isVisible')
            ->willReturn($isVisible);

        $this->_authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn($isAllowed);

        if ($withVisibilityChecker) {
            $item['visible'] = $this->visibilityCheckerMock;
        }

        $urlReturnValueMap = [
            ['*/*/test1', [], 'http://localhost/index.php/backend/admin/test/test1'],
            ['*/*/test2', [], 'http://localhost/index.php/backend/admin/test/test2'],
        ];
        $this->_urlModelMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap($urlReturnValueMap);

        $this->_block->addItem($itemId, $item);
        $this->assertEquals($count, $this->_block->getCount(), $itemId);
    }

    /**
     * @return array
     */
    public function addItemDataProvider()
    {
        return [
            [
                'itemId' => 'test1',
                'item' => ['label' => 'Test 1', 'url' => '*/*/test1'],
                'count' => 1,
                'withVisibilityChecker' => false,
                'isVisible' => false,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'test2',
                'item' => ['label' => 'Test 2', 'url' => '*/*/test2'],
                'count' => 1,
                'withVisibilityChecker' => false,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'test3',
                'item' => ['label' => 'Test 3. Hide', 'url' => '*/*/test3'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => false,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'test4',
                'item' => ['label' => 'Test 4. Does not hide', 'url' => '*/*/test4'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'enable',
                'item' => ['label' => 'Test 5. Not restricted', 'url' => '*/*/test5'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'enable',
                'item' => ['label' => 'Test 5. restricted', 'url' => '*/*/test5'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => false,
            ],
            [
                'itemId' => 'refresh',
                'item' => ['label' => 'Test 6. Not Restricted', 'url' => '*/*/test6'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'refresh',
                'item' => ['label' => 'Test 6. Restricted', 'url' => '*/*/test6'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => false,
            ],
        ];
    }
}
