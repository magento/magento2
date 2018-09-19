<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Helper\Action;

class PagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Review\Helper\Action\Pager */
    protected $_helper = null;

    /**
     * Prepare helper object
     */
    protected function setUp()
    {
        $sessionMock = $this->getMockBuilder(
            \Magento\Backend\Model\Session::class
        )->disableOriginalConstructor()->setMethods(
            ['setData', 'getData']
        )->getMock();
        $sessionMock->expects(
            $this->any()
        )->method(
            'setData'
        )->with(
            $this->equalTo('search_result_idsreviews'),
            $this->anything()
        );
        $sessionMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            $this->equalTo('search_result_idsreviews')
        )->will(
            $this->returnValue([3, 2, 6, 5])
        );

        $contextMock = $this->createPartialMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getModuleManager', 'getRequest']
        );
        $this->_helper = new \Magento\Review\Helper\Action\Pager($contextMock, $sessionMock);
        $this->_helper->setStorageId('reviews');
    }

    /**
     * Test storage set with proper parameters
     */
    public function testStorageSet()
    {
        $result = $this->_helper->setItems([1]);
        $this->assertEquals($result, $this->_helper);
    }

    /**
     * Test getNextItem
     */
    public function testGetNextItem()
    {
        $this->assertEquals(2, $this->_helper->getNextItemId(3));
    }

    /**
     * Test getNextItem when item not found or no next item
     */
    public function testGetNextItemNotFound()
    {
        $this->assertFalse($this->_helper->getNextItemId(30));
        $this->assertFalse($this->_helper->getNextItemId(5));
    }

    /**
     * Test getPreviousItemId
     */
    public function testGetPreviousItem()
    {
        $this->assertEquals(2, $this->_helper->getPreviousItemId(6));
    }

    /**
     * Test getPreviousItemId when item not found or no next item
     */
    public function testGetPreviousItemNotFound()
    {
        $this->assertFalse($this->_helper->getPreviousItemId(30));
        $this->assertFalse($this->_helper->getPreviousItemId(3));
    }
}
