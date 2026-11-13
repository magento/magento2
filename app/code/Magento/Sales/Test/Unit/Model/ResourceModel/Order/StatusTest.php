<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Status as StatusModel;
use Magento\Sales\Model\Order\StatusLabel;
use Magento\Sales\Model\ResourceModel\Order\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class StatusTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Status
     */
    protected $model;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var Mysql|MockObject
     */
    protected $connectionMock;

    /**
     * @var Select
     */
    protected $selectMock;

    /**
     * @var StatusLabel|MockObject
     */
    private $statusLabelMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->selectMock = $this->createMock(Select::class);
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where');

        $this->connectionMock = $this->createPartialMock(
            Mysql::class,
            ['update', 'insertOnDuplicate', 'select', 'delete', 'insertMultiple']
        );
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getTableName')->willReturnCallback(
            static function ($tableName) {
                return [
                    'sales_order_status' => 'sales_order_status',
                    'sales_order_status_label' => 'sales_order_status_label',
                    'sales_order_status_state' => 'sales_order_status_state',
                ][$tableName] ?? null;
            }
        );
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn(
                $this->connectionMock
            );

        $this->configMock = $this->createPartialMockWithReflection(Config::class, ['getConnectionName']);
        $objectManager = new ObjectManager($this);
        $this->statusLabelMock = $this->createMock(StatusLabel::class);
        $context = $objectManager->getObject(Context::class, ['resource' => $this->resourceMock]);
        $this->model = $objectManager->getObject(
            Status::class,
            ['context' => $context, 'statusLabel' => $this->statusLabelMock]
        );
    }

    /**
     * @return void
     */
    public function testAssignState(): void
    {
        $state = 'processing';
        $status = 'processing';
        $isDefault = 1;
        $visibleOnFront = 1;
        $tableName = 'sales_order_status_state';
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $tableName,
                ['is_default' => 0],
                ['state = ?' => $state]
            );
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $tableName,
                [
                    'status' => $status,
                    'state' => $state,
                    'is_default' => $isDefault,
                    'visible_on_front' => $visibleOnFront
                ]
            );
        $this->model->assignState($status, $state, $isDefault, $visibleOnFront);
    }

    /**
     * @param string $hook
     * @param array $data
     * @param int $inserts
     * @return void
     */
    #[DataProvider('writeProvider')]
    public function testWriteClearsLabels(string $hook, array $data, int $inserts): void
    {
        $this->statusLabelMock->expects($this->once())->method('clearCache');
        $deletes = $hook === 'afterSave' && isset($data['store_labels']) ? 1 : 0;
        $this->connectionMock->expects($this->exactly($deletes))->method('delete')
            ->with('sales_order_status_label', ['status = ?' => 'custom']);
        $this->connectionMock->expects($this->exactly($inserts))->method('insertMultiple')
            ->with('sales_order_status_label', [['status' => 'custom', 'store_id' => 1, 'label' => 'Store label']]);
        $status = $this->getMockBuilder(StatusModel::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $status->setData($data + ['status' => 'custom']);

        $this->model->{$hook}($status);
    }

    /**
     * @return array
     */
    public static function writeProvider(): array
    {
        return [
            'replace labels' => ['afterSave', ['store_labels' => [1 => 'Store label']], 1],
            'remove labels' => ['afterSave', ['store_labels' => []], 0],
            'default label only' => ['afterSave', ['label' => 'Default'], 0],
            'delete status' => ['afterDelete', [], 0],
        ];
    }
}
