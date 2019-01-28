<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Area;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\PasswordResetRequestEvent;

/**
 * Test class for \Magento\Security\Model\Plugin\AccountManagement testing
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Security\Model\Plugin\AccountManagement
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Security\Model\SecurityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityManager;

    /**
     * @var AccountManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagement;

    /**
     * @var ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scope;

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

        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->securityManager = $this->createPartialMock(
            \Magento\Security\Model\SecurityManager::class,
            ['performSecurityCheck']
        );

        $this->accountManagement = $this->createMock(AccountManagement::class);
        $this->scope = $this->createMock(ScopeInterface::class);
    }

    /**
     * @param $area
     * @param $passwordRequestEvent
     * @param $expectedTimes
     * @dataProvider beforeInitiatePasswordResetDataProvider
     */
    public function testBeforeInitiatePasswordReset($area, $passwordRequestEvent, $expectedTimes)
    {
        $email = 'test@example.com';
        $template = AccountManagement::EMAIL_RESET;

        $this->model = $this->objectManager->getObject(
            \Magento\Security\Model\Plugin\AccountManagement::class,
            [
                'passwordRequestEvent' => $passwordRequestEvent,
                'request' => $this->request,
                'securityManager' => $this->securityManager,
                'scope' => $this->scope
            ]
        );

        $this->scope->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($area);

        $this->securityManager->expects($this->exactly($expectedTimes))
            ->method('performSecurityCheck')
            ->with($passwordRequestEvent, $email)
            ->willReturnSelf();

        $this->model->beforeInitiatePasswordReset(
            $this->accountManagement,
            $email,
            $template
        );
    }

    /**
     * @return array
     */
    public function beforeInitiatePasswordResetDataProvider()
    {
        return [
            [Area::AREA_ADMINHTML, PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST, 0],
            [Area::AREA_ADMINHTML, PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST, 1],
            [Area::AREA_FRONTEND, PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST, 1],
            // This should never happen, but let's cover it with tests
            [Area::AREA_FRONTEND, PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST, 1],
        ];
    }
}
