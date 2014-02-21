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

class StoreViewTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Code\Plugin\InvocationChain
     */
    protected $pluginMock;

    /**
     * @var StoreView
     */
    protected $model;

    protected function setUp()
    {
        $this->pluginMock = $this->getMock(
            'Magento\Code\Plugin\InvocationChain', array('proceed'), array(), '', false
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            array(), '', false, false, true, array('getId', 'getState', '__wakeup')
        );
        $this->stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Category\Flat\State', array('isFlatEnabled'), array(), '', false
        );
        $this->model = new StoreView(
            $this->indexerMock,
            $this->stateMock
        );
    }

    public function testAroundSaveNewObject()
    {
        $this->mockConfigFlatEnabled();
        $this->mockIndexerMethods();
        $storeMock = $this->getMock(
            'Magento\Core\Model\Store', array('isObjectNew', 'dataHasChangedFor', '__wakeup'), array(), '', false
        );
        $storeMock->expects($this->once())
            ->method('isObjectNew')
            ->will($this->returnValue(true));
        $arguments = array($storeMock);
        $this->mockPluginProceed($arguments);
        $this->assertFalse($this->model->aroundSave($arguments, $this->pluginMock));
    }

    public function testAroundSaveHasChanged()
    {
        $storeMock = $this->getMock(
            'Magento\Core\Model\Store', array('isObjectNew', 'dataHasChangedFor', '__wakeup'), array(), '', false
        );
        $storeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->will($this->returnValue(true));
        $arguments = array($storeMock);
        $this->mockPluginProceed($arguments);
        $this->assertFalse($this->model->aroundSave($arguments, $this->pluginMock));
    }

    public function testAroundSaveNoNeed()
    {
        $this->mockConfigFlatEnabledNeever();
        $storeMock = $this->getMock(
            'Magento\Core\Model\Store', array('isObjectNew', 'dataHasChangedFor', '__wakeup'), array(), '', false
        );
        $storeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->will($this->returnValue(false));
        $arguments = array($storeMock);
        $this->mockPluginProceed($arguments);
        $this->assertFalse($this->model->aroundSave($arguments, $this->pluginMock));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\Indexer\State
     */
    protected function getStateMock()
    {
        $stateMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State', array('setStatus', 'save', '__wakeup'), array(), '', false
        );
        $stateMock->expects($this->once())
            ->method('setStatus')
            ->with('invalid')
            ->will($this->returnSelf());
        $stateMock->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());

        return $stateMock;
    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())
            ->method('invalidate');
    }

    protected function mockConfigFlatEnabled()
    {
        $this->stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->will($this->returnValue(true));
    }

    protected function mockConfigFlatEnabledNeever()
    {
        $this->stateMock->expects($this->never())
            ->method('isFlatEnabled');
    }

    protected function mockPluginProceed($arguments, $returnValue = false)
    {
        $this->pluginMock->expects($this->once())
            ->method('proceed')
            ->with($arguments)
            ->will($this->returnValue($returnValue));
    }
}