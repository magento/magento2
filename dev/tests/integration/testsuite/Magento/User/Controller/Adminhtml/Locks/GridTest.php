<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Testing the list of locked users.
 *
 * @magentoAppArea adminhtml
 */
class GridTest extends AbstractBackendController
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
            '/<td data-column\="username"\s*class\="[^"]*col-name[^"]*col-username[^"]*"\s*>\s*adminUser1\s*<\/td>/',
            $body
        );
        $this->assertRegExp(
            '/<td data-column\="username"\s*class\="[^"]*col-name[^"]*col-username[^"]*"\s*>\s*adminUser2\s*<\/td>/',
            $body
        );
    }
}
