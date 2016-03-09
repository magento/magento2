<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Controller\Adminhtml\Session;

use Magento\TestFramework\Helper\Bootstrap;

class CheckTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Set up
     */
    protected function setUp()
    {
        $this->uri = 'backend/security/session/check';
        parent::setUp();
    }

    /**
     * checkAction test
     */
    public function testCheckAction()
    {
        $this->dispatch('backend/security/session/check');
        $body = $this->getResponse()->getBody();
        $logoutMessage = Bootstrap::getObjectManager()->get('Magento\Framework\Json\Helper\Data')->jsonDecode($body);
        $this->assertTrue($logoutMessage['isActive']);
    }
}
