<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\ResourceModel\Log as CaptchaLog;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

/**
 * Class ResetAttemptForBackendObserverTest
 *
 * Test for checking that the admin login attempts are removed after a successful login
 */
class ResetAttemptForBackendObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Captcha/_files/failed_logins_backend.php
     */
    public function testLoginAttemptsRemovedAfterSuccessfulLogin()
    {
        $login = 'mageadmin';
        $userFactory = $this->objectManager->get(UserFactory::class);
        $captchaLogFactory = $this->objectManager->get(LogFactory::class);
        $eventManager = $this->objectManager->get(ManagerInterface::class);

        /** @var User $user */
        $user = $userFactory->create();
        $user->setUserName($login);

        $eventManager->dispatch(
            'backend_auth_user_login_success',
            ['user' => $user]
        );

        /**
         * @var CaptchaLog $captchaLog
         */
        $captchaLog = $captchaLogFactory->create();

        self::assertEquals(0, $captchaLog->countAttemptsByUserLogin($login));
    }
}
