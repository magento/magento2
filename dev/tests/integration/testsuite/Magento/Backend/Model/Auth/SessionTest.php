<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Auth;

use Magento\TestFramework\Bootstrap as TestHelper;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->auth = $this->objectManager->create(\Magento\Backend\Model\Auth::class);
        $this->authSession = $this->objectManager->create(\Magento\Backend\Model\Auth\Session::class);
        $this->auth->setAuthStorage($this->authSession);
        $this->auth->logout();
    }

    protected function tearDown()
    {
        $this->auth = null;
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)->setCurrentScope(null);
    }

    /**
     * @dataProvider loginDataProvider
     */
    public function testIsLoggedIn($loggedIn)
    {
        if ($loggedIn) {
            $this->auth->login(
                TestHelper::ADMIN_NAME,
                TestHelper::ADMIN_PASSWORD
            );
        }
        $this->assertEquals($loggedIn, $this->authSession->isLoggedIn());
    }

    public function loginDataProvider()
    {
        return [[false], [true]];
    }
}
