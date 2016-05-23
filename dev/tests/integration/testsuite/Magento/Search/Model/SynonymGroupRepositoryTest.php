<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

class SynonymGroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymGroupRepository
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get('Magento\Search\Model\SynonymGroupRepository');
    }

    public function testSaveCreate()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup);
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($synonymGroup->getGroupId());
        $this->assertEquals('a,b,c', $synonymGroupModel->getSynonymGroup());
        $this->assertEquals(0, $synonymGroupModel->getStoreId());
        $this->assertEquals(0, $synonymGroupModel->getWebsiteId());
        $synonymGroupModel->delete();
    }

    /**
     * @expectedException \Magento\Search\Model\Synonym\MergeConflictException
     * @expectedExceptionMessage (a,b,c), (d,e,f)
     */
    public function testSaveCreateMergeConflict()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup);
        $id1 = $synonymGroup->getGroupId();
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('d,e,f');
        $this->model->save($synonymGroup);
        $id2 = $synonymGroup->getGroupId();
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,d,z');
        try {
            $this->model->save($synonymGroup, true);
        } catch (\Magento\Search\Model\Synonym\MergeConflictException $e) {
            /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
            $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
            $synonymGroupModel->load($id1);
            $synonymGroupModel->delete();
            $synonymGroupModel->load($id2);
            $synonymGroupModel->delete();
            throw $e;
        }
    }

    public function testSaveUpdate()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup);
        $id = $synonymGroup->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $updateSynonymGroup */
        $updateSynonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $updateSynonymGroup->setGroupId($id);
        $updateSynonymGroup->setSynonymGroup('d,e,f');
        $this->model->save($updateSynonymGroup);

        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id);
        $this->assertEquals('d,e,f', $synonymGroupModel->getSynonymGroup());
        $this->assertEquals(0, $synonymGroupModel->getStoreId());
        $this->assertEquals(0, $synonymGroupModel->getWebsiteId());
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id);
        $synonymGroupModel->delete();
    }

    /**
     * @expectedException \Magento\Search\Model\Synonym\MergeConflictException
     * @expectedExceptionMessage (d,e,f)
     */
    public function testSaveUpdateMergeConflict()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup);
        $id1 = $synonymGroup->getGroupId();
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('d,e,f');
        $this->model->save($synonymGroup);
        $id2 = $synonymGroup->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $updateSynonymGroup */
        $updateSynonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $updateSynonymGroup->setGroupId($id1);
        $updateSynonymGroup->setSynonymGroup('a,b,d');
        try {
            $this->model->save($updateSynonymGroup, true);
        } catch (\Magento\Search\Model\Synonym\MergeConflictException $e) {
            /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
            $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
            $synonymGroupModel->load($id1);
            $synonymGroupModel->delete();
            $synonymGroupModel->load($id2);
            $synonymGroupModel->delete();
            throw $e;
        }
    }

    public function testSaveCreateMerge()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup1 */
        $synonymGroup1 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup1->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup1);
        $id1 = $synonymGroup1->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup2 */
        $synonymGroup2 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup2->setSynonymGroup('d,e,f');
        $this->model->save($synonymGroup2);
        $id2 = $synonymGroup2->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup3 */
        $synonymGroup3 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup3->setSynonymGroup('g,h,i');
        $this->model->save($synonymGroup3);
        $id3 = $synonymGroup3->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup4 */
        $synonymGroup4 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup4->setSynonymGroup('a,d,z');
        // merging a,b,c and d,e,f with a,d,z
        $this->model->save($synonymGroup4);
        $id4 = $synonymGroup4->getGroupId();

        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id4);
        $this->assertEquals('a,b,c,d,e,f,z', $synonymGroupModel->getSynonyms());
        $this->assertEquals(0, $synonymGroupModel->getStoreId());
        $this->assertEquals(0, $synonymGroupModel->getWebsiteId());
        $synonymGroupModel->delete();

        // g,h,i should not be merged
        $synonymGroupModel->load($id3);
        $this->assertEquals('g,h,i', $synonymGroupModel->getSynonyms());
        $synonymGroupModel->delete();

        // merged rows should be deleted
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id1);
        $this->assertEquals(null, $synonymGroupModel->getSynonyms());

        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id2);
        $this->assertEquals(null, $synonymGroupModel->getSynonyms());
    }

    public function testSaveUpdateMerge()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup1 */
        $synonymGroup1 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup1->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup1);
        $id1 = $synonymGroup1->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup2 */
        $synonymGroup2 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup2->setSynonymGroup('d,e,f');
        $this->model->save($synonymGroup2);
        $id2 = $synonymGroup2->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup3 */
        $synonymGroup3 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup3->setSynonymGroup('g,h,i');
        $this->model->save($synonymGroup3);
        $id3 = $synonymGroup3->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup4 */
        $synonymGroup4 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup4->setSynonymGroup('j,k,l');
        $this->model->save($synonymGroup4);
        $id4 = $synonymGroup4->getGroupId();

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup5 */
        $synonymGroup5 = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup5->setSynonymGroup('a,d,g,z');
        $synonymGroup5->setGroupId($id1);
        // updates a,b,c to a,d,g,z
        $this->model->save($synonymGroup5);

        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id1);
        $this->assertEquals('d,e,f,g,h,i,a,z', $synonymGroupModel->getSynonymGroup());
        $this->assertEquals(0, $synonymGroupModel->getStoreId());
        $this->assertEquals(0, $synonymGroupModel->getWebsiteId());
        $synonymGroupModel->delete();

        // j,k,l is not merged
        $synonymGroupModel->load($id4);
        $this->assertEquals('j,k,l', $synonymGroupModel->getSynonymGroup());
        $synonymGroupModel->delete();

        // no new group is inserted
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id4 + 1);
        $this->assertNull($synonymGroupModel->getSynonymGroup());

        // merged rows are deleted
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id2);
        $this->assertEquals(null, $synonymGroupModel->getSynonyms());

        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id3);
        $this->assertEquals(null, $synonymGroupModel->getSynonyms());
    }

    public function testDelete()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('test1,test2,test3');
        $this->model->save($synonymGroup);
        $id = $synonymGroup->getGroupId();

        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id);

        $this->model->delete($synonymGroupModel);

        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load($id);
        $this->assertNull($synonymGroupModel->getSynonymGroup());
    }
}
