<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

class SynonymGroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymGroupRepository
     */
    private $object;

    /**
     * @var \Magento\Search\Model\SynonymGroupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var \Magento\Search\Model\ResourceModel\SynonymGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceModel;

    public function setUp()
    {
        $this->factory = $this->getMock('Magento\Search\Model\SynonymGroupFactory', ['create'], [], '', false);
        $this->resourceModel = $this->getMock('Magento\Search\Model\ResourceModel\SynonymGroup', [], [], '', false);
        $this->object = new \Magento\Search\Model\SynonymGroupRepository($this->factory, $this->resourceModel);
    }

    public function testSaveCreate()
    {
        $synonymGroupModel = $this->getMock('Magento\Search\Model\SynonymGroup', [], [], '', false);
        $synonymGroupModel->expects($this->once())->method('load');
        $synonymGroupModel->expects($this->once())->method('getSynonymGroup')->willReturn(null);
        $this->factory->expects($this->exactly(2))->method('create')->willReturn($synonymGroupModel);
        $this->resourceModel->expects($this->once())->method('getByScope')->willReturn([]);
        $synonymGroupModel->expects($this->once())->method('setStoreId');
        $synonymGroupModel->expects($this->once())->method('setWebsiteId');
        $synonymGroupModel->expects($this->once())->method('setSynonymGroup');
        $this->resourceModel->expects($this->once())->method('save')->with($synonymGroupModel);

        $data = $this->getMockForAbstractClass('Magento\Search\Api\Data\SynonymGroupInterface', [], '', false);
        $data->expects($this->once())->method('getGroupId');
        $data->expects($this->exactly(2))->method('getStoreId');
        $data->expects($this->exactly(2))->method('getWebsiteId');
        $data->expects($this->once())->method('getSynonymGroup');

        $this->object->save($data);
    }

    /**
     * @expectedException \Magento\Search\Model\Synonym\MergeConflictException
     * @expecteExceptionMessage (c,d,e)
     */
    public function testSaveCreateMergeConflict()
    {
        $synonymGroupModel = $this->getMock('Magento\Search\Model\SynonymGroup', [], [], '', false);
        $synonymGroupModel->expects($this->once())->method('load');
        $synonymGroupModel->expects($this->once())->method('getSynonymGroup')->willReturn(null);
        $this->factory->expects($this->once())->method('create')->willReturn($synonymGroupModel);
        $this->resourceModel->expects($this->once())
            ->method('getByScope')
            ->willReturn([['group_id' => 1, 'synonyms' => 'a,b,c']]);
        $this->resourceModel->expects($this->never())->method('save');

        $data = $this->getMockForAbstractClass('Magento\Search\Api\Data\SynonymGroupInterface', [], '', false);
        $data->expects($this->once())->method('getGroupId');
        $data->expects($this->once())->method('getStoreId');
        $data->expects($this->once())->method('getWebsiteId');
        $data->expects($this->once())->method('getSynonymGroup')->willReturn('c,d,e');

        $this->object->save($data, true);
    }

    public function testSaveUpdate()
    {
        $synonymGroupModel = $this->getMock('Magento\Search\Model\SynonymGroup', [], [], '', false);
        $synonymGroupModel->expects($this->once())->method('load');
        $synonymGroupModel->expects($this->exactly(2))->method('getSynonymGroup')->willReturn('a,b,c');
        $synonymGroupModel->expects($this->once())->method('getGroupId')->willReturn(1);
        $this->factory->expects($this->once())->method('create')->willReturn($synonymGroupModel);
        $this->resourceModel->expects($this->once())->method('getByScope')->willReturn([]);
        $synonymGroupModel->expects($this->once())->method('setStoreId');
        $synonymGroupModel->expects($this->once())->method('setWebsiteId');
        $synonymGroupModel->expects($this->once())->method('setSynonymGroup');
        $this->resourceModel->expects($this->once())->method('save')->with($synonymGroupModel);

        $data = $this->getMockForAbstractClass('Magento\Search\Api\Data\SynonymGroupInterface', [], '', false);
        $data->expects($this->once())->method('getGroupId');
        $data->expects($this->exactly(2))->method('getStoreId');
        $data->expects($this->exactly(2))->method('getWebsiteId');
        $data->expects($this->once())->method('getSynonymGroup')->willReturn('d,e,f');

        $this->object->save($data);
    }

    /**
     * @expectedException \Magento\Search\Model\Synonym\MergeConflictException
     * @expecteExceptionMessage (d,h,i)
     */
    public function testSaveUpdateMergeConflict()
    {
        $synonymGroupModel = $this->getMock('Magento\Search\Model\SynonymGroup', [], [], '', false);
        $synonymGroupModel->expects($this->once())->method('load');
        $synonymGroupModel->expects($this->exactly(2))->method('getSynonymGroup')->willReturn('a,b,c');
        $synonymGroupModel->expects($this->once())->method('getGroupId')->willReturn(1);
        $this->factory->expects($this->once())->method('create')->willReturn($synonymGroupModel);
        $this->resourceModel->expects($this->once())
            ->method('getByScope')
            ->willReturn([['group_id' => 2, 'synonyms' => 'd,h,i']]);
        $this->resourceModel->expects($this->never())->method('save');

        $data = $this->getMockForAbstractClass('Magento\Search\Api\Data\SynonymGroupInterface', [], '', false);
        $data->expects($this->once())->method('getGroupId');
        $data->expects($this->once())->method('getStoreId');
        $data->expects($this->once())->method('getWebsiteId');
        $data->expects($this->once())->method('getSynonymGroup')->willReturn('c,d,e');

        $this->object->save($data, true);
    }
}
