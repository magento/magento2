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
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

class StoreGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $stateMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    protected function setUp()
    {
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getId', 'getState', '__wakeup')
        );
        $this->stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Category\Flat\State',
            array('isFlatEnabled'),
            array(),
            '',
            false
        );
        $this->subjectMock = $this->getMock('Magento\Store\Model\Resource\Group', array(), array(), '', false);

        $this->groupMock = $this->getMock(
            'Magento\Store\Model\Group',
            array('dataHasChangedFor', 'isObjectNew', '__wakeup'),
            array(),
            '',
            false
        );
        $this->closureMock = function () {
            return false;
        };
        $this->model = new StoreGroup($this->indexerMock, $this->stateMock);
    }

    public function testAroundSave()
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->groupMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'root_category_id'
        )->will(
            $this->returnValue(true)
        );
        $this->groupMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(false));
        $this->assertFalse($this->model->aroundSave($this->subjectMock, $this->closureMock, $this->groupMock));
    }

    public function testAroundSaveNotNew()
    {
        $this->stateMock->expects($this->never())->method('isFlatEnabled');
        $this->groupMock = $this->getMock(
            'Magento\Store\Model\Group',
            array('dataHasChangedFor', 'isObjectNew', '__wakeup'),
            array(),
            '',
            false
        );
        $this->groupMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'root_category_id'
        )->will(
            $this->returnValue(true)
        );
        $this->groupMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $this->assertFalse($this->model->aroundSave($this->subjectMock, $this->closureMock, $this->groupMock));
    }
}
