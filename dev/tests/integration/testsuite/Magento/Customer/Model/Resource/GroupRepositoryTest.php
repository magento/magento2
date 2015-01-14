<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Resource;

use Magento\Customer\Api\Data\GroupInterface;

/**
 * Integration test for \Magento\Customer\Model\Resource\GroupRepository
 */
class GroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** The group id of the "NOT LOGGED IN" group */
    const NOT_LOGGED_IN_GROUP_ID = 0;

    /** @var \Magento\Customer\Api\GroupRepositoryInterface */
    private $groupRepository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Customer\Model\Data\GroupBuilder */
    private $groupBuilder;

    /** @var  \Magento\Framework\Api\SearchCriteriaDataBuilder */
    private $searchCriteriaBuilder;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->groupRepository = $this->objectManager->create('Magento\Customer\Api\GroupRepositoryInterface');
        $this->groupBuilder = $this->objectManager->create('Magento\Customer\Api\Data\GroupDataBuilder');
        $this->searchCriteriaBuilder = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaDataBuilder');
    }

    /**
     * @param array $testGroup
     * @dataProvider getGroupsDataProvider
     */
    public function testGetGroup($testGroup)
    {
        $group = $this->groupRepository->getById($testGroup[GroupInterface::ID]);
        $this->assertEquals($testGroup[GroupInterface::ID], $group->getId());
        $this->assertEquals($testGroup[GroupInterface::CODE], $group->getCode());
        $this->assertEquals($testGroup[GroupInterface::TAX_CLASS_ID], $group->getTaxClassId());
    }

    /**
     * @return array
     */
    public function getGroupsDataProvider()
    {
        return [
            [[GroupInterface::ID => 0, GroupInterface::CODE => 'NOT LOGGED IN', GroupInterface::TAX_CLASS_ID => 3]],
            [[GroupInterface::ID => 1, GroupInterface::CODE => 'General', GroupInterface::TAX_CLASS_ID => 3]],
            [[GroupInterface::ID => 2, GroupInterface::CODE => 'Wholesale', GroupInterface::TAX_CLASS_ID => 3]],
            [[GroupInterface::ID => 3, GroupInterface::CODE => 'Retailer', GroupInterface::TAX_CLASS_ID => 3]],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 9999
     */
    public function testGetGroupException()
    {
        $this->groupRepository->getById(9999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateGroup()
    {
        $group = $this->groupBuilder->setId(null)->setCode('Create Group')->setTaxClassId(3)->create();
        $groupId = $this->groupRepository->save($group)->getId();
        $this->assertNotNull($groupId);

        $newGroup = $this->groupRepository->getById($groupId);
        $this->assertEquals($groupId, $newGroup->getId());
        $this->assertEquals($group->getCode(), $newGroup->getCode());
        $this->assertEquals($group->getTaxClassId(), $newGroup->getTaxClassId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateGroupDefaultTaxClass()
    {
        $group = $this->groupBuilder->setId(null)->setCode('Create Group')->setTaxClassId(null)->create();
        $groupId = $this->groupRepository->save($group)->getId();
        $this->assertNotNull($groupId);

        $newGroup = $this->groupRepository->getById($groupId);
        $this->assertEquals($groupId, $newGroup->getId());
        $this->assertEquals($group->getCode(), $newGroup->getCode());
        $this->assertEquals(GroupRepository::DEFAULT_TAX_CLASS_ID, $newGroup->getTaxClassId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testUpdateGroup()
    {
        $group = $this->groupBuilder->setId(null)->setCode('New Group')->setTaxClassId(3)->create();
        $groupId = $this->groupRepository->save($group)->getId();
        $this->assertNotNull($groupId);

        $newGroup = $this->groupRepository->getById($groupId);
        $this->assertEquals($groupId, $newGroup->getId());
        $this->assertEquals($group->getCode(), $newGroup->getCode());
        $this->assertEquals($group->getTaxClassId(), $newGroup->getTaxClassId());

        $updates = $this->groupBuilder->setId($groupId)->setCode('Updated Group')->setTaxClassId(3)->create();
        $this->assertNotNull($this->groupRepository->save($updates));
        $updatedGroup = $this->groupRepository->getById($groupId);
        $this->assertEquals($updates->getCode(), $updatedGroup->getCode(), 'Code not updated.');
        $this->assertEquals($updates->getTaxClassId(), $updatedGroup->getTaxClassId(), 'Tax Class should not change.');
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "9999" provided for the taxClassId field.
     */
    public function testUpdateGroupException()
    {
        $group = $this->groupBuilder->setId(null)->setCode('New Group')->setTaxClassId(3)->create();
        $groupId = $this->groupRepository->save($group)->getId();
        $this->assertNotNull($groupId);

        $newGroup = $this->groupRepository->getById($groupId);
        $this->assertEquals($groupId, $newGroup->getId());
        $this->assertEquals($group->getCode(), $newGroup->getCode());
        $this->assertEquals($group->getTaxClassId(), $newGroup->getTaxClassId());

        $updates = $this->groupBuilder->setId($groupId)->setCode('Updated Group')->setTaxClassId(9999)->create();
        $this->groupRepository->save($updates);
        $updatedGroup = $this->groupRepository->getById($groupId);
        $this->assertEquals($updates->getCode(), $updatedGroup->getCode());
        $this->assertEquals($updates->getTaxClassId(), $updatedGroup->getTaxClassId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDelete()
    {
        $group = $this->groupBuilder->setId(null)->setCode('New Group')->setTaxClassId(3)->create();
        $groupId = $this->groupRepository->save($group)->getId();
        $newGroup = $this->groupRepository->getById($groupId);
        $this->assertTrue($this->groupRepository->delete($newGroup));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteById()
    {
        $group = $this->groupBuilder->setId(null)->setCode('New Group')->setTaxClassId(3)->create();
        $groupId = $this->groupRepository->save($group)->getId();
        $this->assertTrue($this->groupRepository->deleteById($groupId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 9999
     */
    public function testDeleteDoesNotExist()
    {
        $this->assertFalse($this->groupRepository->deleteById(9999));
    }

    public function testGetAllGroups()
    {
        $searchResults = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        /** @var GroupInterface[] $results */
        $results = $searchResults->getItems();
        $this->assertEquals(4, count($results));
    }

    /**
     * @param array $filters
     * @param array $filterGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchGroupsDataProvider
     */
    public function testGetList($filters, $filterGroup, $expectedResult)
    {
        foreach ($filters as $filter) {
            $this->searchCriteriaBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $this->searchCriteriaBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->groupRepository->getList($this->searchCriteriaBuilder->create());

        /** @var $item GroupInterface */
        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals($expectedResult[$item->getId()][GroupInterface::CODE], $item->getCode());
            $this->assertEquals($expectedResult[$item->getId()][GroupInterface::TAX_CLASS_ID], $item->getTaxClassId());
            unset($expectedResult[$item->getId()]);
        }
    }

    public function searchGroupsDataProvider()
    {
        $builder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Api\FilterBuilder');
        return [
            'eq' => [
                [$builder->setField(GroupInterface::CODE)->setValue('General')->create()],
                null,
                [1 => [GroupInterface::CODE => 'General', GroupInterface::TAX_CLASS_ID => 3]],
            ],
            'and' => [
                [
                    $builder->setField(GroupInterface::CODE)->setValue('General')->create(),
                    $builder->setField(GroupInterface::TAX_CLASS_ID)->setValue('3')->create(),
                    $builder->setField(GroupInterface::ID)->setValue('1')->create(),
                ],
                [],
                [1 => [GroupInterface::CODE => 'General', GroupInterface::TAX_CLASS_ID => 3]],
            ],
            'or' => [
                [],
                [
                    $builder->setField(GroupInterface::CODE)->setValue('General')->create(),
                    $builder->setField(GroupInterface::CODE)->setValue('Wholesale')->create(),
                ],
                [
                    1 => [GroupInterface::CODE => 'General', GroupInterface::TAX_CLASS_ID => 3],
                    2 => [GroupInterface::CODE => 'Wholesale', GroupInterface::TAX_CLASS_ID => 3]
                ],
            ],
            'like' => [
                [
                    $builder->setField(GroupInterface::CODE)->setValue('er')->setConditionType('like')
                        ->create(),
                ],
                [],
                [
                    1 => [GroupInterface::CODE => 'General', GroupInterface::TAX_CLASS_ID => 3],
                    3 => [GroupInterface::CODE => 'Retailer', GroupInterface::TAX_CLASS_ID => 3]
                ],
            ],
        ];
    }
}
