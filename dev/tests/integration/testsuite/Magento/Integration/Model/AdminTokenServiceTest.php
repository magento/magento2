<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User as UserModel;

/**
 * Test class for \Magento\Integration\Model\AdminTokenService.
 */
class AdminTokenServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Api\AdminTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * Setup AdminTokenService
     */
    public function setUp()
    {
        $this->tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\AdminTokenService::class);
        $this->tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
        $this->userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);
    }

    /**
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testCreateAdminAccessToken()
    {
        $adminUserNameFromFixture = 'adminUser';
        $accessToken = $this->tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $adminUserId = $this->userModel->loadByUsername($adminUserNameFromFixture)->getId();
        /** @var $token TokenModel */
        $token = $this->tokenModel
            ->loadByAdminId($adminUserId)
            ->getToken();
        $this->assertEquals($accessToken, $token);
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testCreateAdminAccessTokenEmptyOrNullCredentials($username, $password)
    {
        try {
            $this->tokenService->createAdminAccessToken($username, $password);
        } catch (InputException $e) {
            $this->assertInputExceptionMessages($e);
        }
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     */
    public function testCreateAdminAccessTokenInvalidCustomer()
    {
        $adminUserName = 'invalid';
        $password = 'invalid';
        $this->tokenService->createAdminAccessToken($adminUserName, $password);

        $this->expectExceptionMessage(
            'The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.'
        );
    }

    /**
     * Provider to test input validation
     *
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            'Check for empty credentials' => ['', ''],
            'Check for null credentials' => [null, null]
        ];
    }

    /**
     * Assert for presence of Input exception messages
     *
     * @param InputException $e
     */
    private function assertInputExceptionMessages($e)
    {
        $this->assertEquals('One or more input exceptions have occurred.', $e->getMessage());
        $errors = $e->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('"username" is required. Enter and try again.', $errors[0]->getLogMessage());
        $this->assertEquals('"password" is required. Enter and try again.', $errors[1]->getLogMessage());
    }
}
