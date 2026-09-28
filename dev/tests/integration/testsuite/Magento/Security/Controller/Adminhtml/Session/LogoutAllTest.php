<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Security\Controller\Adminhtml\Session;

class LogoutAllTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->uri = 'backend/security/session/logoutAll';
        parent::setUp();
    }

    /**
     * logoutAllAction test
     */
    public function testLogoutAllAction()
    {
        $this->dispatch('backend/security/session/logoutAll');
        $this->assertSessionMessages(
            $this->equalTo(['All other open sessions for this account were terminated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('security/session/activity'));
    }
}
