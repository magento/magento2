<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Resource;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class EntitySnapshotTest
 */
class EntitySnapshotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\EntitySnapshot
     */
    protected $entitySnapshot;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\Resource\EntityMetadata
     */
    protected $entityMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\AbstractModel
     */
    protected $model;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $this->getMock(
            'Magento\Sales\Model\AbstractModel',
            [],
            [],
            '',
            false
        );

        $this->entityMetadata = $this->getMock(
            'Magento\Sales\Model\Resource\EntityMetadata',
            [],
            [],
            '',
            false
        );

        $this->entitySnapshot = $objectManager->getObject(
            'Magento\Sales\Model\Resource\EntitySnapshot',
            ['entityMetadata' => $this->entityMetadata]
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
            'id',
            'name',
            'description'
        ];
        $this->model->expects($this->once())->method('getData')->willReturn($data);
        $this->model->expects($this->once())->method('getId')->willReturn($entityId);
        $this->entityMetadata->expects($this->once())->method('getFields')->with($this->model)->willReturn($fields);
        $this->entitySnapshot->registerSnapshot($this->model);
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
        $this->model->expects($this->exactly(4))->method('getData')->willReturnOnConsecutiveCalls(
            $data,
            $modifiedData,
            $modifiedData,
            $modifiedData
        );
        $this->model->expects($this->any())->method('getId')->willReturn($entityId);
        $this->entityMetadata->expects($this->exactly(4))->method('getFields')->with($this->model)->willReturn($fields);
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->assertTrue($this->entitySnapshot->isModified($this->model));
        $this->entitySnapshot->registerSnapshot($this->model);
        $this->assertFalse($this->entitySnapshot->isModified($this->model));
    }
}
