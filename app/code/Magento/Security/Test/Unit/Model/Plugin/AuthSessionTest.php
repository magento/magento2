<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\Plugin\AuthSession testing
 */
class AuthSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Model\Plugin\AuthSession
     */
    protected $model;

    /**
     * @var \Magento\Security\Model\AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $session;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $currentSession;

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
            ['getCurrentSession', 'processProlong'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMockForAbstractClass(
            '\Magento\Framework\App\RequestInterface',
            ['getParam', 'getModuleName', 'getActionName'],
            '',
            false
        );

        $this->session =  $this->getMock(
            '\Magento\Backend\Model\Auth\Session',
            ['destroy'],
            [],
            '',
            false
        );

        $this->currentSession =  $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            ['isActive'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\Plugin\AuthSession',
            [
                'sessionsManager' => $this->sessionsManager,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testAroundProlongIsNotAjaxRequest()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };
        $this->sessionsManager->expects($this->any())
            ->method('getCurrentSession')
            ->willReturn($this->currentSession);
        $this->currentSession->expects($this->any())
            ->method('isActive')
            ->willReturn(false);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(false);
        $this->session->expects($this->once())
            ->method('destroy');

        $this->model->aroundProlong($this->session, $proceed);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsActive()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };
        $this->sessionsManager->expects($this->any())
            ->method('getCurrentSession')
            ->willReturn($this->currentSession);
        $this->currentSession->expects($this->any())
            ->method('isActive')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('getModuleName')
            ->willReturn('notSecurity');
        $this->requestMock->expects($this->any())
            ->method('getActionName')
            ->willReturn('notCheck');
        $this->sessionsManager->expects($this->any())
            ->method('processProlong');

        $this->assertEquals($result, $this->model->aroundProlong($this->session, $proceed));
    }
}
