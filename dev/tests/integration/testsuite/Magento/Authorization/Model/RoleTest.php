<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model;

/**
 * @magentoAppArea adminhtml
 */
class RoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\Role
     */
    protected $_model = null;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Authorization\Model\Role::class);
    }

    public function testGetRoleUsers()
    {
        $this->assertEmpty($this->_model->getRoleUsers());

        $this->_model->load(\Magento\TestFramework\Bootstrap::ADMIN_ROLE_NAME, 'role_name');
        $this->assertNotEmpty($this->_model->getRoleUsers());
    }
}
