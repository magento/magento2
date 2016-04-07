<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Widget\Grid\Massaction
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

class MassactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Massaction
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_gridMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_gridMock = $this->getMock(
            'Magento\Backend\Block\Widget\Grid',
            ['getId', 'getCollection'],
            [],
            '',
            false
        );
        $this->_gridMock->expects($this->any())->method('getId')->will($this->returnValue('test_grid'));

        $this->_layoutMock = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getParentName', 'getBlock', 'helper'],
            [],
            '',
            false,
            false
        );

        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getParentName'
        )->with(
            'test_grid_massaction'
        )->will(
            $this->returnValue('test_grid')
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getBlock'
        )->with(
            'test_grid'
        )->will(
            $this->returnValue($this->_gridMock)
        );

        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $this->_urlModelMock = $this->getMock('Magento\Backend\Model\Url', [], [], '', false);

        $arguments = [
            'layout' => $this->_layoutMock,
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlModelMock,
            'data' => ['massaction_id_field' => 'test_id', 'massaction_id_filter' => 'test_id'],
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject('Magento\Backend\Block\Widget\Grid\Massaction', $arguments);
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
     * @param $itemId
     * @param $item
     * @param $expectedItem \Magento\Framework\DataObject
     * @dataProvider itemsDataProvider
     */
    public function testItemsProcessing($itemId, $item, $expectedItem)
    {
        $this->_urlModelMock->expects(
            $this->any()
        )->method(
            'getBaseUrl'
        )->will(
            $this->returnValue('http://localhost/index.php')
        );

        $urlReturnValueMap = [
            ['*/*/test1', [], 'http://localhost/index.php/backend/admin/test/test1'],
            ['*/*/test2', [], 'http://localhost/index.php/backend/admin/test/test2'],
        ];
        $this->_urlModelMock->expects($this->any())->method('getUrl')->will($this->returnValueMap($urlReturnValueMap));

        $this->_block->addItem($itemId, $item);
        $this->assertEquals(1, $this->_block->getCount());

        $actualItem = $this->_block->getItem($itemId);
        $this->assertInstanceOf('Magento\Framework\DataObject', $actualItem);
        $this->assertEquals($expectedItem->getData(), $actualItem->getData());

        $this->_block->removeItem($itemId);
        $this->assertEquals(0, $this->_block->getCount());
        $this->assertNull($this->_block->getItem($itemId));
    }

    public function itemsDataProvider()
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
     * @param $param
     * @param $expectedJson
     * @param $expected
     * @dataProvider selectedDataProvider
     */
    public function testSelected($param, $expectedJson, $expected)
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            $this->_block->getFormFieldNameInternal()
        )->will(
            $this->returnValue($param)
        );

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

        $collectionMock = $this->getMockBuilder('Magento\Framework\Data\Collection')
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
}
