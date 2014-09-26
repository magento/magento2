<?php
/**
 * Integration test for service layer \Magento\Customer\Service\Customer
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

use Magento\Customer\Service\V1\Data\CustomerGroup;
use Magento\Framework\Service\V1\Data\FilterBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Service\V1\Data\Filter;
use Magento\Customer\Model\Group;

class CustomerGroupServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_groupService = null;

    protected function setUp()
    {

        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_groupService = $this->_objectManager->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
    }

    protected function tearDown()
    {
        $this->_objectManager = null;
        $this->_groupService = null;
    }

    /**
     * Cleaning up the extra groups that might have been created as part of the testing.
     */
    public static function tearDownAfterClass()
    {
        /** @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface $customerGroupService */
        $customerGroupService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerGroupServiceInterface'
        );
        foreach ($customerGroupService->getGroups() as $group) {
            if ($group->getId() > 3) {
                $customerGroupService->deleteGroup($group->getId());
            }
        }
    }

    /**
     */
    public function testGetGroups()
    {
        $groups = $this->_groupService->getGroups();
        $this->assertEquals(4, count($groups));
        $this->assertEquals(
            [0, 'NOT LOGGED IN', 3],
            [$groups[0]->getId(), $groups[0]->getCode(), $groups[0]->getTaxClassId()]
        );
        $this->assertEquals(
            [1, 'General', 3],
            [$groups[1]->getId(), $groups[1]->getCode(), $groups[1]->getTaxClassId()]
        );
        $this->assertEquals(
            [2, 'Wholesale', 3],
            [$groups[2]->getId(), $groups[2]->getCode(), $groups[2]->getTaxClassId()]
        );
        $this->assertEquals(
            [3, 'Retailer', 3],
            [$groups[3]->getId(), $groups[3]->getCode(), $groups[3]->getTaxClassId()]
        );
    }

    /**
     */
    public function testGetGroupsFiltered()
    {
        $groups = $this->_groupService->getGroups(false);
        $this->assertEquals(3, count($groups));
        $this->assertEquals(
            [1, 'General', 3],
            [$groups[0]->getId(), $groups[0]->getCode(), $groups[0]->getTaxClassId()]
        );
        $this->assertEquals(
            [2, 'Wholesale', 3],
            [$groups[1]->getId(), $groups[1]->getCode(), $groups[1]->getTaxClassId()]
        );
        $this->assertEquals(
            [3, 'Retailer', 3],
            [$groups[2]->getId(), $groups[2]->getCode(), $groups[2]->getTaxClassId()]
        );
    }

    /**
     * @param $testGroup
     * @dataProvider getGroupsDataProvider
     */
    public function testGetGroup($testGroup)
    {
        $group = $this->_groupService->getGroup($testGroup[CustomerGroup::ID]);
        $this->assertEquals($testGroup[CustomerGroup::ID], $group->getId());
        $this->assertEquals($testGroup[CustomerGroup::CODE], $group->getCode());
        $this->assertEquals($testGroup[CustomerGroup::TAX_CLASS_ID], $group->getTaxClassId());
    }

    /**
     * @return array
     */
    public function getGroupsDataProvider()
    {
        return [ [[CustomerGroup::ID => 0, CustomerGroup::CODE => 'NOT LOGGED IN', CustomerGroup::TAX_CLASS_ID => 3]],
            [[CustomerGroup::ID => 1, CustomerGroup::CODE => 'General', CustomerGroup::TAX_CLASS_ID => 3]],
            [[CustomerGroup::ID => 2, CustomerGroup::CODE => 'Wholesale', CustomerGroup::TAX_CLASS_ID => 3]],
            [[CustomerGroup::ID => 3, CustomerGroup::CODE => 'Retailer', CustomerGroup::TAX_CLASS_ID => 3]],
        ];
    }

    public function testCreateGroup()
    {
        $builder = $this->_objectManager->create('\Magento\Customer\Service\V1\Data\CustomerGroupBuilder');
        $group = $builder->setId(null)->setCode('Test Group')->setTaxClassId(3)->create();
        $groupId = $this->_groupService->createGroup($group);
        $this->assertNotNull($groupId);

        $newGroup = $this->_groupService->getGroup($groupId);
        $this->assertEquals($groupId, $newGroup->getId());
        $this->assertEquals($group->getCode(), $newGroup->getCode());
        $this->assertEquals($group->getTaxClassId(), $newGroup->getTaxClassId());
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage ID is not expected for this request.
     * @magentoDbIsolation enabled
     */
    public function testCreateGroupWithId()
    {
        $builder = $this->_objectManager->create('\Magento\Customer\Service\V1\Data\CustomerGroupBuilder');
        $group = $builder->setId(88)->setCode('Test Create Group With Id')->setTaxClassId(3)->create();
        $this->_groupService->createGroup($group);
    }

    public function testUpdateGroup()
    {
        $builder = $this->_objectManager->create('\Magento\Customer\Service\V1\Data\CustomerGroupBuilder');
        $group = $builder->setId(null)->setCode('New Group')->setTaxClassId(3)->create();
        $groupId = $this->_groupService->createGroup($group);
        $this->assertNotNull($groupId);

        $newGroup = $this->_groupService->getGroup($groupId);
        $this->assertEquals($groupId, $newGroup->getId());
        $this->assertEquals($group->getCode(), $newGroup->getCode());
        $this->assertEquals($group->getTaxClassId(), $newGroup->getTaxClassId());

        $updates = $builder->setId($groupId)->setCode('Updated Group')->setTaxClassId(3)
            ->create();
        $this->assertTrue($this->_groupService->updateGroup($groupId, $updates));
        $updatedGroup = $this->_groupService->getGroup($groupId);
        $this->assertEquals($updates->getCode(), $updatedGroup->getCode());
        $this->assertEquals($updates->getTaxClassId(), $updatedGroup->getTaxClassId());
    }


    /**
     * @param $testGroup
     * @param $storeId
     *
     * @dataProvider getDefaultGroupDataProvider
     */
    public function testGetDefaultGroupWithStoreId($testGroup, $storeId)
    {
        $this->assertDefaultGroupMatches($testGroup, $storeId);
    }


    /**
     * @return array
     *
     */
    public function getDefaultGroupDataProvider()
    {
        /** @var \Magento\Framework\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface');
        $defaultStoreId = $storeManager->getStore()->getId();
        return [
            'no store id' => [['id' => 1, 'code' => 'General', 'tax_class_id' => 3], null],
            'default store id' => [['id' => 1, 'code' => 'General', 'tax_class_id' => 3], $defaultStoreId],
        ];
    }

    /**
     * @magentoDataFixture Magento/Core/_files/second_third_store.php
     */
    public function testGetDefaultGroupWithNonDefaultStoreId()
    {        /** @var \Magento\Framework\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface');
        $nonDefaultStore = $storeManager->getStore('secondstore');
        $nonDefaultStoreId = $nonDefaultStore->getId();
        /** @var \Magento\Framework\App\MutableScopeConfig $scopeConfig */
        $scopeConfig = $this->_objectManager->get('Magento\Framework\App\MutableScopeConfig');
        $scopeConfig->setValue(Group::XML_PATH_DEFAULT_ID, 2, ScopeInterface::SCOPE_STORE, 'secondstore');
        $testGroup = ['id' => 2, 'code' => 'Wholesale', 'tax_class_id' => 3];
        $this->assertDefaultGroupMatches($testGroup, $nonDefaultStoreId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetDefaultGroupWithInvalidStoreId()
    {
        $storeId = 1234567;
        $this->_groupService->getDefaultGroup($storeId);
    }

    /**
     * @param Filter[] $filters
     * @param Filter[] $filterGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchGroupsDataProvider
     */
    public function testSearchGroups($filters, $filterGroup, $expectedResult)
    {
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Service\V1\Data\SearchCriteriaBuilder');
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->_groupService->searchGroups($searchBuilder->create());

        /** @var $item Data\CustomerGroup */
        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals($expectedResult[$item->getId()][CustomerGroup::CODE], $item->getCode());
            $this->assertEquals($expectedResult[$item->getId()][CustomerGroup::TAX_CLASS_ID], $item->getTaxClassId());
            unset($expectedResult[$item->getId()]);
        }
    }

    public function searchGroupsDataProvider()
    {
        $builder = Bootstrap::getObjectManager()->create('\Magento\Framework\Service\V1\Data\FilterBuilder');
        return [
            'eq' => [
                [$builder->setField(CustomerGroup::CODE)->setValue('General')->create()],
                null,
                [1 => [CustomerGroup::CODE => 'General', CustomerGroup::TAX_CLASS_ID => 3]]
            ],
            'and' => [
                [
                    $builder->setField(CustomerGroup::CODE)->setValue('General')->create(),
                    $builder->setField(CustomerGroup::TAX_CLASS_ID)->setValue('3')->create(),
                    $builder->setField(CustomerGroup::ID)->setValue('1')->create(),
                ],
                [],
                [1 => [CustomerGroup::CODE => 'General', CustomerGroup::TAX_CLASS_ID => 3]]
            ],
            'or' => [
                [],
                [
                    $builder->setField(CustomerGroup::CODE)->setValue('General')->create(),
                    $builder->setField(CustomerGroup::CODE)->setValue('Wholesale')->create(),
                ],
                [
                    1 => [CustomerGroup::CODE => 'General', CustomerGroup::TAX_CLASS_ID => 3],
                    2 => [CustomerGroup::CODE => 'Wholesale', CustomerGroup::TAX_CLASS_ID => 3]
                ]
            ],
            'like' => [
                [
                    $builder->setField(CustomerGroup::CODE)->setValue('er')->setConditionType('like')
                        ->create()
                ],
                [],
                [
                    1 => [CustomerGroup::CODE => 'General', CustomerGroup::TAX_CLASS_ID => 3],
                    3 => [CustomerGroup::CODE => 'Retailer', CustomerGroup::TAX_CLASS_ID => 3]
                ]
            ],
        ];
    }

    /**
     * @param $testGroup
     * @param $storeId
     */
    private function assertDefaultGroupMatches($testGroup, $storeId)
    {
        $group = $this->_groupService->getDefaultGroup($storeId);
        $this->assertEquals($testGroup['id'], $group->getId());
        $this->assertEquals($testGroup['code'], $group->getCode());
        $this->assertEquals($testGroup['tax_class_id'], $group->getTaxClassId());
    }
}
