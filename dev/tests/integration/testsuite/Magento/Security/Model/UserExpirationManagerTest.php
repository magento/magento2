<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * TODO: test logging out sessions
 * TODO: test that you cannot create a token for an expired user (where would I put this test?)
 * TODO: test AdminSessionsManager::processLogin
 * TODO: test AdminSessionsManager::processProlong (do it here or in the AdminSessionsManagerTest?)
 *
 * @magentoAppArea adminhtml
 */
class UserExpirationManagerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testUserIsExpired()
    {
        $adminUserNameFromFixture = 'adminUserExpired';
        $user = $this->getUserFromUserName($adminUserNameFromFixture);
        /** @var \Magento\Security\Model\UserExpirationManager $userExpirationManager */
        $userExpirationManager = Bootstrap::getObjectManager()
            ->create(\Magento\Security\Model\UserExpirationManager::class);
        static::assertTrue($userExpirationManager->userIsExpired($user));
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testDeactivateExpiredUsersWithExpiredUser()
    {
        $adminUserNameFromFixture = 'adminUserExpired';

        list($user, $token, $expiredUserModel) = $this->setupCronTests($adminUserNameFromFixture);

        static::assertEquals(0, $user->getIsActive());
        static::assertEquals(null, $token->getId());
        static::assertNull($expiredUserModel->getId());
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testDeactivateExpiredUsersWithNonExpiredUser()
    {
        $adminUserNameFromFixture = 'adminUserNotExpired';
        // log them in
        $adminToken = $this->createToken($adminUserNameFromFixture);

        list($user, $token, $expiredUserModel) = $this->setupCronTests($adminUserNameFromFixture);

        static::assertEquals(1, $user->getIsActive());
        static::assertNotNull($token->getId());
        static::assertEquals($expiredUserModel->getUserId(), $user->getId());
    }

    /**
     * @param string $adminUserNameFromFixture
     * @return array
     */
    private function setupCronTests(string $adminUserNameFromFixture): array
    {
        // TODO: set the user expired after calling this
        // TODO: use this to test the observer with exception:
        // Magento\Framework\Exception\Plugin\AuthenticationException : The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.
        //$adminToken = $this->createToken($adminUserNameFromFixture); // TODO: this logs the user in, which kicks off the deactivate call

        /** @var \Magento\Security\Model\UserExpirationManager $job */
        $userExpirationManager = Bootstrap::getObjectManager()
            ->create(\Magento\Security\Model\UserExpirationManager::class);
        $userExpirationManager->deactivateExpiredUsers();

        /** @var \Magento\User\Model\User $user */
        $user = $this->getUserFromUserName($adminUserNameFromFixture);

        // this is for the API only
        $oauthToken = $this->getOauthTokenByUser($user);
        $expiredUserModel = $this->getExpiredUserModelByUser($user);

        return [$user, $oauthToken, $expiredUserModel];
    }

    /**
     * TODO: this calls user->login and throws an AuthenticationException
     *
     * @param string $adminUserNameFromFixture
     * @return string
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createToken(string $adminUserNameFromFixture): string
    {
        /** @var \Magento\Integration\Api\AdminTokenServiceInterface $tokenService */
        $tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Api\AdminTokenServiceInterface::class);
        $token = $tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        return $token;
    }

    /**
     * @param string $adminUserNameFromFixture
     * @return \Magento\User\Model\User
     */
    private function getUserFromUserName(string $adminUserNameFromFixture): \Magento\User\Model\User
    {
        /** @var \Magento\User\Model\User $user */
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);
        return $user;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return \Magento\Integration\Model\Oauth\Token
     */
    private function getOauthTokenByUser(\Magento\User\Model\User $user): \Magento\Integration\Model\Oauth\Token
    {
        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
        $oauthToken = $tokenModel->loadByAdminId($user->getId());
        return $oauthToken;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return UserExpiration
     */
    private function getExpiredUserModelByUser(\Magento\User\Model\User $user): \Magento\Security\Model\UserExpiration
    {
        /** @var \Magento\Security\Model\UserExpiration $expiredUserModel */
        $expiredUserModel = Bootstrap::getObjectManager()->get(\Magento\Security\Model\UserExpiration::class);
        $expiredUserModel->load($user->getId());
        return $expiredUserModel;
    }
}
