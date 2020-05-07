<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractModelTest extends TestCase
{
    /**
     * @var AbstractModel
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var MockObject
     */
    protected $actionValidatorMock;

    protected function setUp(): void
    {
        $this->actionValidatorMock = $this->createMock(RemoveAction::class);
        $this->contextMock = new Context(
            $this->getMockForAbstractClass(LoggerInterface::class),
            $this->getMockForAbstractClass(ManagerInterface::class),
            $this->getMockForAbstractClass(CacheInterface::class),
            $this->createMock(State::class),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->createMock(Registry::class);
        $this->resourceMock = $this->createPartialMock(AbstractDb::class, [
            '_construct',
            'getConnection',
            '__wakeup',
            'commit',
            'delete',
            'getIdFieldName',
            'rollBack'
        ]);
        $this->resourceCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = $this->getMockForAbstractClass(
            AbstractModel::class,
            [$this->contextMock, $this->registryMock, $this->resourceMock, $this->resourceCollectionMock]
        );
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
    }

    public function testDelete()
    {
        $this->resourceMock->expects($this->once())->method('delete')->with($this->model);
        $this->model->delete();
    }

    public function testUpdateStoredData()
    {
        $this->model->setData(
            [
                'id'   => 1000,
                'name' => 'Test Name'
            ]
        );
        $this->assertEmpty($this->model->getStoredData());
        $this->model->afterLoad();
        $this->assertEquals($this->model->getData(), $this->model->getStoredData());
        $this->model->setData('value', 'Test Value');
        $this->model->afterSave();
        $this->assertEquals($this->model->getData(), $this->model->getStoredData());
        $this->model->afterDelete();
        $this->assertEmpty($this->model->getStoredData());
    }

    /**
     * Tests \Magento\Framework\DataObject->isDeleted()
     */
    public function testIsDeleted()
    {
        $this->assertFalse($this->model->isDeleted());
        $this->model->isDeleted();
        $this->assertFalse($this->model->isDeleted());
        $this->model->isDeleted(true);
        $this->assertTrue($this->model->isDeleted());
    }

    /**
     * Tests \Magento\Framework\DataObject->hasDataChanges()
     */
    public function testHasDataChanges()
    {
        $this->assertFalse($this->model->hasDataChanges());
        $this->model->setData('key', 'value');
        $this->assertTrue($this->model->hasDataChanges(), 'Data changed');

        $this->model->setDataChanges(false);
        $this->model->setData('key', 'value');
        $this->assertFalse($this->model->hasDataChanges(), 'Data not changed');

        $this->model->setData(['key' => 'value']);
        $this->assertFalse($this->model->hasDataChanges(), 'Data not changed (array)');

        $this->model->unsetData();
        $this->assertTrue($this->model->hasDataChanges(), 'Unset data');
    }

    /**
     * Tests \Magento\Framework\DataObject->getId()
     */
    public function testSetGetId()
    {
        $this->model->setId('test');
        $this->assertEquals('test', $this->model->getId());
    }

    public function testSetGetIdFieldName()
    {
        $name = 'entity_id_custom';
        $this->model->setIdFieldName($name);
        $this->assertEquals($name, $this->model->getIdFieldName());
    }

    /**
     * Tests \Magento\Framework\DataObject->setOrigData()
     */
    public function testOrigData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $this->model->setData($data);
        $this->model->setOrigData();
        $this->model->setData('key1', 'test');
        $this->assertTrue($this->model->dataHasChangedFor('key1'));
        $this->assertEquals($data, $this->model->getOrigData());

        $this->model->setOrigData('key1', 'test');
        $this->assertEquals('test', $this->model->getOrigData('key1'));
    }

    /**
     * Tests \Magento\Framework\DataObject->setDataChanges()
     */
    public function testSetDataChanges()
    {
        $this->assertFalse($this->model->hasDataChanges());
        $this->model->setDataChanges(true);
        $this->assertTrue($this->model->hasDataChanges());
    }
}
