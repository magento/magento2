<?php
/**
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

namespace Magento\Customer\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for Magento\Customer\Model\GroupManagement
 */
class GroupManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $groupManagement;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->groupManagement = $this->objectManager->get('Magento\Customer\Api\GroupManagementInterface');
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
     * @magentoDataFixture Magento/Core/_files/second_third_store.php
     */
    public function testGetDefaultGroupWithNonDefaultStoreId()
    {        /** @var \Magento\Framework\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface');
        $nonDefaultStore = $storeManager->getStore('secondstore');
        $nonDefaultStoreId = $nonDefaultStore->getId();
        /** @var \Magento\Framework\App\MutableScopeConfig $scopeConfig */
        $scopeConfig = $this->objectManager->get('Magento\Framework\App\MutableScopeConfig');
        $scopeConfig->setValue(
            \Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID,
            2,
            ScopeInterface::SCOPE_STORE,
            'secondstore'
        );
        $testGroup = ['id' => 2, 'code' => 'Wholesale', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'];
        $this->assertDefaultGroupMatches($testGroup, $nonDefaultStoreId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetDefaultGroupWithInvalidStoreId()
    {
        $storeId = 1234567;
        $this->groupManagement->getDefaultGroup($storeId);
    }

    public function testIsReadonlyWithGroupId()
    {
        $testGroup = ['id' => 3, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'];
        $this->assertEquals(true, $this->groupManagement->isReadonly($testGroup['id']));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testIsReadonlyWithInvalidGroupId()
    {
        $testGroup = ['id' => 4, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'];
        $this->groupManagement->isReadonly($testGroup['id']);
    }

    /**
     * @return array
     */
    public function getDefaultGroupDataProvider()
    {
        /** @var \Magento\Framework\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface');
        $defaultStoreId = $storeManager->getStore()->getId();
        return [
            'no store id' => [
                ['id' => 1, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'],
                null
            ],
            'default store id' => [
                ['id' => 1, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'],
                $defaultStoreId
            ],
        ];
    }

    /**
     * @param $testGroup
     * @param $storeId
     */
    private function assertDefaultGroupMatches($testGroup, $storeId)
    {
        $group = $this->groupManagement->getDefaultGroup($storeId);
        $this->assertEquals($testGroup['id'], $group->getId());
        $this->assertEquals($testGroup['code'], $group->getCode());
        $this->assertEquals($testGroup['tax_class_id'], $group->getTaxClassId());
        $this->assertEquals($testGroup['tax_class_name'], $group->getTaxClassName());
    }
}
