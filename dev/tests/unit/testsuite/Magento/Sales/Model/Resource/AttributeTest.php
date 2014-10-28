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
namespace Magento\Sales\Model\Resource;

/**
 * Class AttributeTest
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;
    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;
    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelMock;
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    public function setUp()
    {
        $this->appResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->modelMock = $this->getMockForAbstractClass(
            'Magento\Sales\Model\AbstractModel',
            [],
            '',
            false,
            false,
            true,
            ['__wakeup', 'getId', 'getEventPrefix', 'getEventObject']
        );
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['describeTable', 'insert', 'lastInsertId', 'beginTransaction', 'rollback', 'commit'],
            [],
            '',
            false
        );
        $this->adapterMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->adapterMock->expects($this->any())
            ->method('insert');
        $this->adapterMock->expects($this->any())
            ->method('lastInsertId');
        $this->attribute = new \Magento\Sales\Model\Resource\Attribute(
            $this->appResourceMock,
            $this->eventManagerMock
        );
    }

    /**
     * @throws \Exception
     */
    public function testSave()
    {
        $this->appResourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $this->modelMock->expects($this->any())
            ->method('getEventPrefix')
            ->will($this->returnValue('event_prefix'));
        $this->modelMock->expects($this->any())
            ->method('getEventObject')
            ->will($this->returnValue('event_object'));
        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('event_prefix_save_attribute_before', [
                'event_object' => $this->attribute,
                'object' => $this->modelMock,
                'attribute' => ['attribute']
            ]);
        $this->eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('event_prefix_save_attribute_after', [
                'event_object' => $this->attribute,
                'object' => $this->modelMock,
                'attribute' => ['attribute']
            ]);
        $this->adapterMock->expects($this->once())
            ->method('beginTransaction');
        $this->adapterMock->expects($this->once())
            ->method('commit');
        $this->assertEquals($this->attribute, $this->attribute->saveAttribute($this->modelMock, 'attribute'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Expected Exception
     * @throws \Exception
     */
    public function testSaveFailed()
    {
        $this->modelMock->expects($this->any())
            ->method('getEventPrefix')
            ->will($this->returnValue('event_prefix'));
        $this->modelMock->expects($this->any())
            ->method('getEventObject')
            ->will($this->returnValue('event_object'));
        $this->appResourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $exception  = new \Exception('Expected Exception');
        $this->modelMock->expects($this->any())
            ->method('getId')
            ->will($this->throwException($exception ));
        $this->adapterMock->expects($this->once())
            ->method('beginTransaction');
        $this->adapterMock->expects($this->once())
            ->method('rollback');
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'event_prefix_save_attribute_before',
                [
                    'event_object' =>  $this->attribute,
                    'object' => $this->modelMock,
                    'attribute' => ['attribute']
                ]
            );
        $this->attribute->saveAttribute($this->modelMock, 'attribute');
    }
}
