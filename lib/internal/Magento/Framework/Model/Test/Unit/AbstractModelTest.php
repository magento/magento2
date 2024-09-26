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

    /**
     * Test case for checking setData function is working for all possible key value pairs
     *
     * @dataProvider getKeyValueDataPairs
     */
    public function testSetDataWithDifferentKeyValuePairs(
        array $data,
        mixed $testKey,
        mixed $testValue,
        bool $hasDataChangedFor
    ): void {
        $this->model->setData($data);
        $this->model->setOrigData();
        $this->model->setData($testKey, $testValue);
        $this->assertEquals($data, $this->model->getOrigData());
        $this->assertEquals($hasDataChangedFor, $this->model->dataHasChangedFor($testKey));
    }

    /**
     * Data provider for testSetDataWithDifferentKeyValuePairs
     *
     * @return array
     */
    public static function getKeyValueDataPairs(): array
    {
        return [
            'when test data and compare data are string' => [['key' => 'value'], 'key', 'value', false],
            'when test data and compare data are different' => [['key' => 'value'], 'key', 10, true],
            'when test data string and compare data is null' => [['key' => 'value'], 'key', null, true],
            'when test data and compare data both null' => [['key' => null], 'key', null, false],
            'when test data empty string and compare data is null' => [['key' => ''], 'key', null, false],
            'when test data and compare data are empty string' => [['key' => ''], 'key', '', false],
            'when test data is null and compare data is empty string' => [['key' => null], 'key', '', false],
            'when test data and compare data are int' => [['key' => 1], 'key', 1, false],
            'when test data is int and compare data is float' => [['key' => 1.0], 'key', 1, false],
            'when test data is string and compare data is float' => [['key' => '1.0'], 'key', 1.0, false],
            'when test data is string and compare data is int' => [['key' => '1'], 'key', 1, false],
            'when test data is float and compare data is string' => [['key' => 1.0], 'key', '1.0', false],
            'when test data is int and compare data is string' => [['key' => 1], 'key', '1', false],
            'when test data and compare data are float' => [['key' => 1.0], 'key', 1.0, false],
            'when test data is 0 and compare data is null' => [['key' => 0], 'key', null, false],
            'when test data is null and compare data is 0' => [['key' => null], 'key', 0, false],
            'when test data is string array and compare data is int' => [['key' => '10'], 'key', 10, false],
            'when test data is string array and compare data is float' => [['key' => '22.00'], 'key', 22.00, false]
        ];
    }
}
