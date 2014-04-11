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
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

class StoreViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|
     */
    protected $pluginMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

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
        $this->model = new StoreView($this->indexerMock);
        $this->subject = $this->getMock('Magento\Store\Model\Resource\Group', array(), array(), '', false);
    }

    public function testAroundSaveNewObject()
    {
        $this->mockIndexerMethods();
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            array('isObjectNew', 'dataHasChangedFor', '__wakeup'),
            array(),
            '',
            false
        );
        $storeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $proceed = $this->mockPluginProceed();
        $this->assertFalse($this->model->aroundSave($this->subject, $proceed, $storeMock));
    }

    public function testAroundSaveHasChanged()
    {
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            array('isObjectNew', 'dataHasChangedFor', '__wakeup'),
            array(),
            '',
            false
        );
        $storeMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'group_id'
        )->will(
            $this->returnValue(true)
        );
        $proceed = $this->mockPluginProceed();
        $this->assertFalse($this->model->aroundSave($this->subject, $proceed, $storeMock));
    }

    public function testAroundSaveNoNeed()
    {
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            array('isObjectNew', 'dataHasChangedFor', '__wakeup'),
            array(),
            '',
            false
        );
        $storeMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'group_id'
        )->will(
            $this->returnValue(false)
        );
        $proceed = $this->mockPluginProceed();
        $this->assertFalse($this->model->aroundSave($this->subject, $proceed, $storeMock));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\Indexer\State
     */
    protected function getStateMock()
    {
        $stateMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State',
            array('setStatus', 'save', '__wakeup'),
            array(),
            '',
            false
        );
        $stateMock->expects($this->once())->method('setStatus')->with('invalid')->will($this->returnSelf());
        $stateMock->expects($this->once())->method('save')->will($this->returnSelf());

        return $stateMock;
    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())->method('invalidate');
    }

    protected function mockPluginProceed($returnValue = false)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }
}
