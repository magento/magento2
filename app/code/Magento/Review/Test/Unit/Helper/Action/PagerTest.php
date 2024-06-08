<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Helper\Action;

use Magento\Backend\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Review\Helper\Action\Pager;
use PHPUnit\Framework\TestCase;

class PagerTest extends TestCase
{
    /** @var Pager */
    protected $_helper = null;

    /**
     * Prepare helper object
     */
    protected function setUp(): void
    {
        $sessionMock = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->addMethods(['setData'])
            ->onlyMethods(
                ['getData']
            )->getMock();
        $sessionMock->expects(
            $this->any()
        )->method(
            'setData'
        )->with(
            'search_result_idsreviews',
            $this->anything()
        );
        $sessionMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            'search_result_idsreviews'
        )->willReturn(
            [3, 2, 6, 5]
        );

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getModuleManager', 'getRequest']
        );
        $this->_helper = new Pager($contextMock, $sessionMock);
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
