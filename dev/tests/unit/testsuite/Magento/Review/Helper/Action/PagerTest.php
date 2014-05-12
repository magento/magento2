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
namespace Magento\Review\Helper\Action;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Review\Helper\Action\Pager */
    protected $_helper = null;

    /**
     * Prepare helper object
     */
    protected function setUp()
    {
        $sessionMock = $this->getMockBuilder(
            'Magento\Backend\Model\Session'
        )->disableOriginalConstructor()->setMethods(
            array('setData', 'getData')
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
            $this->returnValue(array(3, 2, 6, 5))
        );

        $contextMock = $this->getMock(
            'Magento\Framework\App\Helper\Context',
            array('getModuleManager', 'getRequest'),
            array(),
            '',
            false
        );
        $this->_helper = new \Magento\Review\Helper\Action\Pager($contextMock, $sessionMock);
        $this->_helper->setStorageId('reviews');
    }

    /**
     * Test storage set with proper parameters
     */
    public function testStorageSet()
    {
        $this->_helper->setItems(array(1));
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
