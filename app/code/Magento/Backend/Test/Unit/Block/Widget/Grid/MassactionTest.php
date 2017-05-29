<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Widget\Grid\Massaction
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface as VisibilityChecker;

class MassactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Massaction
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \Magento\Backend\Block\Widget\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_gridMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var VisibilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $visibilityCheckerMock;

    protected function setUp()
    {
        $this->_gridMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['getId', 'getCollection'])
            ->getMock();
        $this->_gridMock->expects($this->any())
            ->method('getId')
            ->willReturn('test_grid');

        $this->_layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
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

        $this->_requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->_urlModelMock = $this->getMockBuilder(\Magento\Backend\Model\Url::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->visibilityCheckerMock = $this->getMockBuilder(VisibilityChecker::class)
            ->getMockForAbstractClass();

        $arguments = [
            'layout' => $this->_layoutMock,
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlModelMock,
            'data' => ['massaction_id_field' => 'test_id', 'massaction_id_filter' => 'test_id']
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(
            \Magento\Backend\Block\Widget\Grid\Massaction::class,
            $arguments
        );
        $this->_block->setNameInLayout('test_grid_massaction');
    }

    protected function tearDown()
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
     * @param \Magento\Framework\DataObject $item
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

        $this->_block->addItem($itemId, $item);
        $this->assertEquals(1, $this->_block->getCount());

        $actualItem = $this->_block->getItem($itemId);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $actualItem);
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
                new \Magento\Framework\DataObject(
                    [
                        "label" => "Test Item One",
                        "url" => "http://localhost/index.php/backend/admin/test/test1",
                        "id" => 'test_id1',
                    ]
                ),
            ],
            [
                'test_id2',
                new \Magento\Framework\DataObject(["label" => "Test Item Two", "url" => "*/*/test2"]),
                new \Magento\Framework\DataObject(
                    [
                        "label" => "Test Item Two",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'test_id2',
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
     * @param array $items
     * @param string $result
     *
     * @dataProvider dataProviderGetGridIdsJsonWithUseSelectAll
     */
    public function testGetGridIdsJsonWithUseSelectAll(array $items, $result)
    {
        $this->_block->setUseSelectAll(true);

        $collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_gridMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('clear')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with(0)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($items);

        $this->assertEquals($result, $this->_block->getGridIdsJson());
    }

    /**
     * @return array
     */
    public function dataProviderGetGridIdsJsonWithUseSelectAll()
    {
        return [
            [
                [],
                '',
            ],
            [
                [1],
                '1',
            ],
            [
                [1, 2, 3],
                '1,2,3',
            ],
        ];
    }

    /**
     * @param string $itemId
     * @param array|\Magento\Framework\DataObject $item
     * @param int $count
     * @param bool $withVisibilityChecker
     * @param bool $isVisible
     * @dataProvider addItemDataProvider
     */
    public function testAddItem($itemId, $item, $count, $withVisibilityChecker, $isVisible)
    {
        $this->visibilityCheckerMock->expects($this->any())
            ->method('isVisible')
            ->willReturn($isVisible);

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
        $this->assertEquals($count, $this->_block->getCount());
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
                '$isVisible' => false,
            ],
            [
                'itemId' => 'test2',
                'item' => ['label' => 'Test 2', 'url' => '*/*/test2'],
                'count' => 1,
                'withVisibilityChecker' => false,
                'isVisible' => true,
            ],
            [
                'itemId' => 'test1',
                'item' => ['label' => 'Test 1. Hide', 'url' => '*/*/test1'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => false,
            ],
            [
                'itemId' => 'test2',
                'item' => ['label' => 'Test 2. Does not hide', 'url' => '*/*/test2'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
            ]
        ];
    }
}
