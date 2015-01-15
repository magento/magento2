<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;


class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionValidatorMock;

    protected function setUp()
    {
        $this->actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction',
            [],
            [],
            '',
            false
        );
        $this->contextMock = new \Magento\Framework\Model\Context(
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\CacheInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\State', [], [], '', false),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
                '__wakeup',
                'commit',
                'delete',
                'getIdFieldName',
                'rollBack'
            ],
            [],
            '',
            false
        );
        $this->resourceCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db',
            [],
            [],
            '',
            false
        );
        $this->model = $this->getMockForAbstractClass(
            'Magento\Framework\Model\AbstractModel',
            [$this->contextMock, $this->registryMock, $this->resourceMock, $this->resourceCollectionMock]
        );
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            [],
            '',
            false
        );
        $this->resourceMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($this->adapterMock));
        $this->resourceMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($this->adapterMock));
    }

    public function testDelete()
    {
        $this->resourceMock->expects($this->once())->method('delete')->with($this->model);
        $this->model->delete();
    }
}
