<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\Plugin\Auth;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\Plugin\Auth testing
 */
class AuthTest extends TestCase
{
    /**
     * @var  Auth
     */
    protected $model;

    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var AdminSessionInfo
     */
    protected $currentSession;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $authMock;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->sessionsManager = $this->createPartialMock(
            AdminSessionsManager::class,
            ['processLogin', 'processLogout', 'getCurrentSession']
        );

        $this->messageManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            ['addWarningMessage'],
            '',
            false
        );

        $this->currentSession = $this->createPartialMock(
            AdminSessionInfo::class,
            ['isOtherSessionsTerminated']
        );

        $this->authMock =  $this->createMock(\Magento\Backend\Model\Auth::class);

        $this->model = $this->objectManager->getObject(
            Auth::class,
            [
                'sessionsManager' => $this->sessionsManager,
                'messageManager' =>$this->messageManager
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterLogin()
    {
        $warningMessage = __('All other open sessions for this account were terminated.');
        $this->sessionsManager->expects($this->once())
            ->method('processLogin');
        $this->sessionsManager->expects($this->once())
            ->method('getCurrentSession')
            ->willReturn($this->currentSession);
        $this->currentSession->expects($this->once())
            ->method('isOtherSessionsTerminated')
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addWarningMessage')
            ->with($warningMessage);

        $this->model->afterLogin($this->authMock);
    }

    /**
     * @return void
     */
    public function testBeforeLogout()
    {
        $this->sessionsManager->expects($this->once())->method('processLogout');
        $this->model->beforeLogout($this->authMock);
    }
}
