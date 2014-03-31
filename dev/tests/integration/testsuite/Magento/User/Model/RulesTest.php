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
 * @category    Magento
 * @package     Magento_User
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Model;

/**
 * @magentoAppArea adminhtml
 */
class RulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Model\Rules
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\Rules'
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCRUD()
    {
        $this->_model->setRoleType(
            'G'
        )->setResourceId(
            'Magento_Adminhtml::all'
        )->setPrivileges(
            ""
        )->setAssertId(
            0
        )->setRoleId(
            1
        )->setPermission(
            'allow'
        );

        $crud = new \Magento\TestFramework\Entity($this->_model, array('permission' => 'deny'));
        $crud->testCrud();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testInitialUserPermissions()
    {
        $adapter = $this->_model->getResource()->getReadConnection();
        $ruleSelect = $adapter->select()->from($this->_model->getResource()->getMainTable());

        $rules = $ruleSelect->query()->fetchAll();
        $this->assertEquals(1, count($rules));
        $this->assertEquals('Magento_Adminhtml::all', $rules[0]['resource_id']);
        $this->assertEquals(1, $rules[0]['role_id']);
        $this->assertEquals('allow', $rules[0]['permission']);
    }

    /**
     * @covers \Magento\User\Model\Rules::saveRel
     * @magentoDbIsolation enabled
     */
    public function testSetAllowForAllResources()
    {
        $adapter = $this->_model->getResource()->getReadConnection();
        $ruleSelect = $adapter->select()->from($this->_model->getResource()->getMainTable());

        $resources = array('Magento_Adminhtml::all');

        $this->_model->setRoleId(1)->setResources($resources)->saveRel();

        $rules = $ruleSelect->query()->fetchAll();
        $this->assertEquals(1, count($rules));
        $this->assertEquals('Magento_Adminhtml::all', $rules[0]['resource_id']);
        $this->assertEquals(1, $rules[0]['role_id']);
        $this->assertEquals('allow', $rules[0]['permission']);
    }
}
