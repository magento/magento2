<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\VersionControl;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SnapshotTest
 */
class SnapshotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot
     */
    protected $entitySnapshot;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata
     */
    protected $entityMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\AbstractModel
     */
    protected $model;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $this->getMock(
            'Magento\Framework\Model\AbstractModel',
            ['getId'],
            [],
            '',
            false
        );

        $this->entityMetadata = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata',
            ['getFields'],
            [],
            '',
            false
        );

        $this->entitySnapshot = $objectManager->getObject(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot',
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
}
