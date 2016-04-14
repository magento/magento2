<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

class GridTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test index action
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/User/_files/locked_users.php
     */
    public function testGridAction()
    {
        $this->dispatch('backend/admin/locks/grid');

        $body = $this->getResponse()->getBody();
        $this->assertContains('data-column="username"', $body);
        $this->assertContains('data-column="last_login"', $body);
        $this->assertContains('data-column="last_login"', $body);
        $this->assertContains('data-column="failures_num"', $body);
        $this->assertContains('data-column="lock_expires"', $body);
        $this->assertRegExp(
            '/<td data-column\="username"\s*class\="\s*col-name\s*col-username\s*"\s*>\s*adminUser1\s*<\/td>/',
            $body
        );
        $this->assertRegExp(
            '/<td data-column\="username"\s*class\="\s*col-name\s*col-username\s*"\s*>\s*adminUser2\s*<\/td>/',
            $body
        );
    }
}
