<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\User\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class MainTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var \Magento\User\Block\User\Edit\Tab\Main
     */
    protected $_block;

    /**
     * @var \Magento\User\Model\User
     */
    protected $_user;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_block = $objectManager->create(\Magento\User\Block\User\Edit\Tab\Main::class);
        $this->_block->setArea('adminhtml');
        $this->_user = $objectManager->create(\Magento\User\Model\User::class);

        $objectManager->get(\Magento\Framework\Registry::class)->register('permissions_user', $this->_user);
    }

    protected function tearDown(): void
    {
        $this->_block = null;
        $this->_user = null;
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('permissions_user');
        parent::tearDown();
    }

    public function testToHtmlPasswordFieldsExistingEntry()
    {
        $this->_user->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);
        $actualHtml = $this->_block->toHtml();
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"required-entry") and @type="password"]',
                $actualHtml
            ),
            'There should be 1 required password entry: current user password.'
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"validate-admin-password") and @type="password" and @name="password"]',
                $actualHtml
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"validate-cpassword") and @type="password" and ' .
                '@name="password_confirmation"]',
                $actualHtml
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"validate-current-password") and @type="password" and @name="'
                . Main::CURRENT_USER_PASSWORD_FIELD . '"]',
                $actualHtml
            )
        );
    }

    public function testToHtmlPasswordFieldsNewEntry()
    {
        $actualHtml = $this->_block->toHtml();
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"validate-admin-password") and contains(@class,"required-entry") and  '
                . '@type="password" and @name="password"]',
                $actualHtml
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"validate-cpassword") and contains(@class,"required-entry") and  '
                . '@type="password" and @name="password_confirmation"]',
                $actualHtml
            )
        );
    }
}
