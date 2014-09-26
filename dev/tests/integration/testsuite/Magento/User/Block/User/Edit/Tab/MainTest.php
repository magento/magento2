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
namespace Magento\User\Block\User\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class MainTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @var \Magento\User\Block\User\Edit\Tab\Main
     */
    protected $_block;

    /**
     * @var \Magento\User\Model\User
     */
    protected $_user;

    protected function setUp()
    {
        parent::setUp();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_block = $objectManager->create('Magento\User\Block\User\Edit\Tab\Main');
        $this->_block->setArea('adminhtml');
        $this->_user = $objectManager->create('Magento\User\Model\User');

        $objectManager->get('Magento\Framework\Registry')->register('permissions_user', $this->_user);
    }

    protected function tearDown()
    {
        $this->_block = null;
        $this->_user = null;
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('permissions_user');
        parent::tearDown();
    }

    public function testToHtmlPasswordFieldsExistingEntry()
    {
        $this->_user->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);
        $actualHtml = $this->_block->toHtml();
        $this->assertSelectCount(
            'input.required-entry[type="password"]',
            1,
            $actualHtml,
            'There should be 1 required password entry: current user password.'
        );
        $this->assertSelectCount('input.validate-admin-password[type="password"][name="password"]', 1, $actualHtml);
        $this->assertSelectCount(
            'input.validate-cpassword[type="password"][name="password_confirmation"]',
            1,
            $actualHtml
        );
        $this->assertSelectCount(
            'input.validate-current-password[type="password"][name="' . Main::CURRENT_USER_PASSWORD_FIELD . '"]',
            1,
            $actualHtml
        );
    }

    public function testToHtmlPasswordFieldsNewEntry()
    {
        $actualHtml = $this->_block->toHtml();
        $this->assertSelectCount(
            'input.validate-admin-password.required-entry[type="password"][name="password"]',
            1,
            $actualHtml
        );
        $this->assertSelectCount(
            'input.validate-cpassword.required-entry[type="password"][name="password_confirmation"]',
            1,
            $actualHtml
        );
    }
}
