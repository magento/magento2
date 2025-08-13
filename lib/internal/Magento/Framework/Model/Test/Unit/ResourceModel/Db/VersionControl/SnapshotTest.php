<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\VersionControl;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SnapshotTest extends TestCase
{
    /**
     * @var Snapshot
     */
    protected Snapshot $entitySnapshot;

    /**
     * @var MockObject|Metadata
     */
    private Metadata $entityMetadata;

    /**
     * @var MockObject|AbstractModel
     */
    private AbstractModel $model;

    /**
     * @var SerializerInterface|MockObject
     */
    private SerializerInterface $serializer;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->model = $this->createPartialMock(AbstractModel::class, ['getId']);

        $this->entityMetadata = $this->createPartialMock(
            Metadata::class,
            ['getFields']
        );
        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->entitySnapshot = new Snapshot($this->entityMetadata, $this->serializer);

        parent::setUp();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testRegisterSnapshot(): void
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

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testIsModified(): void
    {
        $entityId = 1;
        $options = ['option1' => 'value1', 'option2' => 'value2'];
        $data = [
            'id' => $entityId,
            'name' => 'test',
            'description' => '',
            'custom_not_present_attribute' => '',
            'options' => json_encode($options)
        ];
        $fields = [
            'id' => [],
            'name' => [],
            'description' => [],
            'options' => []
        ];
        $modifiedData = array_merge($data, ['name' => 'newName']);
        $this->model->expects($this->any())->method('getId')->willReturn($entityId);
        $this->entityMetadata->expects($this->exactly(2))->method('getFields')->with($this->model)->willReturn($fields);
        $this->model->setData($data);
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->model->setData($modifiedData);
        $this->assertTrue($this->entitySnapshot->isModified($this->model));

        $this->model->setData('options', $options);
        $this->assertTrue($this->entitySnapshot->isModified($this->model));

        $this->entitySnapshot->registerSnapshot($this->model);
        $this->assertFalse($this->entitySnapshot->isModified($this->model));
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testClear(): void
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
