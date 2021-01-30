<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\VersionControl;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SnapshotTest extends TestCase
{
    /**
     * @var Snapshot
     */
    protected $entitySnapshot;

    /**
     * @var MockObject|Metadata
     */
    protected $entityMetadata;

    /**
     * @var MockObject|AbstractModel
     */
    protected $model;

    /**
     * Initialization
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $this->createPartialMock(AbstractModel::class, ['getId']);

        $this->entityMetadata = $this->createPartialMock(
            Metadata::class,
            ['getFields']
        );

        $this->entitySnapshot = $objectManager->getObject(
            Snapshot::class,
            ['metadata' => $this->entityMetadata]
        );
    }

    public function testRegisterSnapshot()
    {
        $entityId = 1;
        $data = [
            'id' => $entityId,
            'name' => 'test',
            'description' => '',
            'custom_not_present_attribute' => ''
        ];
        $fields = [
            'id' => [],
            'name' => [],
            'description' => []
        ];
        $this->assertTrue($this->entitySnapshot->isModified($this->model));
        $this->model->setData($data);
        $this->model->expects($this->any())->method('getId')->willReturn($entityId);
        $this->entityMetadata->expects($this->any())->method('getFields')->with($this->model)->willReturn($fields);
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->assertFalse($this->entitySnapshot->isModified($this->model));
    }

    public function testIsModified()
    {
        $entityId = 1;
        $data = [
            'id' => $entityId,
            'name' => 'test',
            'description' => '',
            'custom_not_present_attribute' => ''
        ];
        $fields = [
            'id' => [],
            'name' => [],
            'description' => []
        ];
        $modifiedData = array_merge($data, ['name' => 'newName']);
        $this->model->expects($this->any())->method('getId')->willReturn($entityId);
        $this->entityMetadata->expects($this->exactly(2))->method('getFields')->with($this->model)->willReturn($fields);
        $this->model->setData($data);
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->model->setData($modifiedData);
        $this->assertTrue($this->entitySnapshot->isModified($this->model));
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->assertFalse($this->entitySnapshot->isModified($this->model));
    }

    public function testClear()
    {
        $entityId = 1;
        $data = [
            'id' => $entityId,
            'name' => 'test',
            'description' => '',
            'custom_not_present_attribute' => ''
        ];
        $fields = [
            'id' => [],
            'name' => [],
            'description' => []
        ];
        $this->assertTrue($this->entitySnapshot->isModified($this->model));
        $this->model->setData($data);
        $this->model->expects($this->any())->method('getId')->willReturn($entityId);
        $this->entityMetadata->expects($this->any())->method('getFields')->with($this->model)->willReturn($fields);
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->entitySnapshot->clear($this->model);
        $this->assertTrue($this->entitySnapshot->isModified($this->model));
    }
}
