<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\Plugin\Auth testing
 */
class AuthTest extends \PHPUnit_Framework_TestCase
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

        $this->sessionsManager =  $this->getMock(
            '\Magento\Security\Model\AdminSessionsManager',
            ['processLogin', 'processLogout', 'getCurrentSession'],
            [],
            '',
            false
        );

        $this->messageManager = $this->getMockForAbstractClass(
            '\Magento\Framework\Message\ManagerInterface',
            ['addWarning'],
            '',
            false
        );

        $this->currentSession =  $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            ['isOtherSessionsTerminated'],
            [],
            '',
            false
        );

        $this->authMock =  $this->getMock(
            '\Magento\Backend\Model\Auth',
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\Plugin\Auth',
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
