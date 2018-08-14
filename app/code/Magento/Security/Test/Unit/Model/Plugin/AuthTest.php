<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\Plugin\Auth testing
 */
class AuthTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Security\Model\Plugin\Auth
     */
    protected $model;

    /**
     * @var \Magento\Security\Model\AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $currentSession;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $authMock;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->sessionsManager = $this->createPartialMock(
            \Magento\Security\Model\AdminSessionsManager::class,
            ['processLogin', 'processLogout', 'getCurrentSession']
        );

        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            ['addWarning'],
            '',
            false
        );

        $this->currentSession = $this->createPartialMock(
            \Magento\Security\Model\AdminSessionInfo::class,
            ['isOtherSessionsTerminated']
        );

        $this->authMock =  $this->createMock(\Magento\Backend\Model\Auth::class);

        $this->model = $this->objectManager->getObject(
            \Magento\Security\Model\Plugin\Auth::class,
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
            ->method('addWarning')
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
