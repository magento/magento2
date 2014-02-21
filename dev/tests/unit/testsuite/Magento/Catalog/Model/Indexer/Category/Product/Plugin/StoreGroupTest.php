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

class StoreGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

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
        $this->model = new StoreGroup(
            $this->indexerMock
        );
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testAroundSave($valueMap)
    {
        $this->mockIndexerMethods();
        $groupMock = $this->getMock(
            'Magento\Core\Model\Store\Group', array('dataHasChangedFor', 'isObjectNew', '__wakeup'), array(), '', false
        );
        $groupMock->expects($this->exactly(2))
            ->method('dataHasChangedFor')
            ->will($this->returnValueMap($valueMap));
        $groupMock->expects($this->once())
            ->method('isObjectNew')
            ->will($this->returnValue(false));

        $arguments = array($groupMock);
        $this->mockPluginProceed($arguments);
        $this->assertFalse($this->model->aroundSave($arguments, $this->pluginMock));
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testAroundSaveNotNew($valueMap)
    {
        $groupMock = $this->getMock(
            'Magento\Core\Model\Store\Group', array('dataHasChangedFor', 'isObjectNew', '__wakeup'), array(), '', false
        );
        $groupMock->expects($this->exactly(2))
            ->method('dataHasChangedFor')
            ->will($this->returnValueMap($valueMap));
        $groupMock->expects($this->once())
            ->method('isObjectNew')
            ->will($this->returnValue(true));

        $arguments = array($groupMock);
        $this->mockPluginProceed($arguments);
        $this->assertFalse($this->model->aroundSave($arguments, $this->pluginMock));
    }

    public function changedDataProvider()
    {
        return array(array(
            array(
                array('root_category_id', true),
                array('website_id', false),
            ),
            array(
                array('root_category_id', false),
                array('website_id', true),
            ),
        ));
    }

    public function testAroundSaveWithoutChanges()
    {
        $groupMock = $this->getMock(
            'Magento\Core\Model\Store\Group', array('dataHasChangedFor', 'isObjectNew', '__wakeup'), array(), '', false
        );
        $groupMock->expects($this->exactly(2))
            ->method('dataHasChangedFor')
            ->will($this->returnValueMap(array(
                array('root_category_id', false),
                array('website_id', false),
            )));
        $groupMock->expects($this->never())
            ->method('isObjectNew');

        $arguments = array($groupMock);
        $this->mockPluginProceed($arguments);
        $this->assertFalse($this->model->aroundSave($arguments, $this->pluginMock));

    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())
            ->method('invalidate');
    }

    protected function mockPluginProceed($arguments, $returnValue = false)
    {
        $this->pluginMock->expects($this->once())
            ->method('proceed')
            ->with($arguments)
            ->will($this->returnValue($returnValue));
    }
}