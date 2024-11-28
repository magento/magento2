<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\SecurityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\Plugin\AccountManagement testing
 */
class AccountManagementTest extends TestCase
{
    /**
     * @var  \Magento\Security\Model\Plugin\AccountManagement
     */
    protected $model;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var SecurityManager|MockObject
     */
    protected $securityManager;

    /**
     * @var AccountManagement|MockObject
     */
    protected $accountManagement;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scope;

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

        $this->request =  $this->getMockForAbstractClass(RequestInterface::class);

        $this->securityManager = $this->createPartialMock(
            SecurityManager::class,
            ['performSecurityCheck']
        );

        $this->accountManagement =  $this->createMock(AccountManagement::class);
        $this->scope =  $this->getMockForAbstractClass(ScopeInterface::class);
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

        $this->scope->expects($this->any())
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
    public static function beforeInitiatePasswordResetDataProvider()
    {
        return [
            [Area::AREA_ADMINHTML, PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST, 0],
            [Area::AREA_ADMINHTML, PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST, 1],
            [Area::AREA_FRONTEND, PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST, 1],
            // This should never happen, but let's cover it with tests
            [Area::AREA_FRONTEND, PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST, 1],
            [Area::AREA_WEBAPI_REST, PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST, 1],
        ];
    }
}
