<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model;

/**
 * @magentoAppArea adminhtml
 */
class RulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\Rules
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Authorization\Model\Rules'
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCRUD()
    {
        $this->_model
            ->setRoleType('G')
            ->setResourceId('Magento_Backend::all')
            ->setPrivileges("")
            ->setAssertId(0)
            ->setRoleId(1)
            ->setPermission('allow');

        $crud = new \Magento\TestFramework\Entity($this->_model, ['permission' => 'deny']);
        $crud->testCrud();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testInitialUserPermissions()
    {
        $expectedDefaultPermissions = ['Magento_Backend::all'];
        $this->_checkExistingPermissions($expectedDefaultPermissions);
    }

    /**
     * @covers \Magento\Authorization\Model\Rules::saveRel
     * @magentoDbIsolation enabled
     */
    public function testSetAllowForAllResources()
    {
        $resources = ['Magento_Backend::all'];
        $this->_model->setRoleId(1)->setResources($resources)->saveRel();
        $expectedPermissions = ['Magento_Backend::all'];
        $this->_checkExistingPermissions($expectedPermissions);
    }

    /**
     * Ensure that only expected permissions are set.
     */
    protected function _checkExistingPermissions($expectedDefaultPermissions)
    {
        $connection = $this->_model->getResource()->getConnection();
        $ruleSelect = $connection->select()->from($this->_model->getResource()->getMainTable());

        $rules = $ruleSelect->query()->fetchAll();
        $this->assertEquals(1, count($rules));
        $actualPermissions = [];
        foreach ($rules as $rule) {
            $actualPermissions[] = $rule['resource_id'];
            $this->assertEquals(
                'allow',
                $rule['permission'],
                "Permission for '{$rule['resource_id']}' resource should be 'allow'"
            );
        }
        $this->assertEquals($expectedDefaultPermissions, $actualPermissions, 'Default permissions are invalid');
    }
}
