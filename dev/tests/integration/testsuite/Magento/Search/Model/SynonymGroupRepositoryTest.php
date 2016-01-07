<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @magentoDbIsolation disabled
 */
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
        $synonymGroupModel->load(1);
        $this->assertEquals('a,b,c', $synonymGroupModel->getSynonyms());
        $this->assertEquals(0, $synonymGroupModel->getStoreId());
        $this->assertEquals(0, $synonymGroupModel->getWebsiteId());
        $synonymGroupModel->delete();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage (a,b,c), (d,e,f)
     */
    public function testSaveCreateMergeConflict()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup);
        $synonymGroup->setSynonymGroup('d,e,f');
        $this->model->save($synonymGroup);
        $synonymGroup->setSynonymGroup('a,d,z');
        try {
            $this->model->save($synonymGroup);
        } catch (\Exception $e) {
            /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
            $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
            $synonymGroupModel->load(2);
            $synonymGroupModel->delete();
            $synonymGroupModel->load(3);
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

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $updateSynonymGroup */
        $updateSynonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $updateSynonymGroup->setGroupId(4);
        $updateSynonymGroup->setSynonymGroup('d,e,f');
        $this->model->save($updateSynonymGroup);

        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load(4);
        $this->assertEquals('d,e,f', $synonymGroupModel->getSynonymGroup());
        $this->assertEquals(0, $synonymGroupModel->getStoreId());
        $this->assertEquals(0, $synonymGroupModel->getWebsiteId());
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
        $synonymGroupModel->load(4);
        $synonymGroupModel->delete();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage (d,e,f)
     */
    public function testSaveUpdateMergeConflict()
    {
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup */
        $synonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $synonymGroup->setSynonymGroup('a,b,c');
        $this->model->save($synonymGroup);
        $synonymGroup->setSynonymGroup('d,e,f');
        $this->model->save($synonymGroup);

        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $updateSynonymGroup */
        $updateSynonymGroup = $this->objectManager->create('Magento\Search\Api\Data\SynonymGroupInterface');
        $updateSynonymGroup->setGroupId(5);
        $updateSynonymGroup->setSynonymGroup('a,b,d');
        try {
            $this->model->save($updateSynonymGroup);
        } catch (\Exception $e) {
            /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel */
            $synonymGroupModel = $this->objectManager->create('Magento\Search\Model\SynonymGroup');
            $synonymGroupModel->load(5);
            $synonymGroupModel->delete();
            $synonymGroupModel->load(6);
            $synonymGroupModel->delete();
            throw $e;
        }
    }
}
