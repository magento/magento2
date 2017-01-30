<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Widget\Grid\Massaction\Extended
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Massaction;

class ExtendedTest extends \PHPUnit_Framework_TestCase
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
        $this->_block = $objectManagerHelper->getObject(
            'Magento\Backend\Block\Widget\Grid\Massaction\Extended',
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
