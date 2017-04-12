<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

/**
 * Test class for \Magento\Backend\Model\Session.
 *
 * @magentoAppArea adminhtml
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        if (array_key_exists('adminhtml', $_SESSION)) {
            unset($_SESSION['adminhtml']);
        }
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Backend\Model\Session::class,
            [$logger]
        );
        $this->assertArrayHasKey('adminhtml', $_SESSION);
    }
}
