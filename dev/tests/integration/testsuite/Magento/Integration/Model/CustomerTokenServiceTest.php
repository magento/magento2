<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Integration\Model\CustomerTokenService.
 */
class CustomerTokenServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * Setup CustomerTokenService
     */
    public function setUp()
    {
        $this->tokenService = Bootstrap::getObjectManager()->get('Magento\Integration\Model\CustomerTokenService');
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $this->tokenModel = Bootstrap::getObjectManager()->get('Magento\Integration\Model\Oauth\Token');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateCustomerAccessToken()
    {
        $customerUserName = 'customer@example.com';
        $password = 'password';
        $accessToken = $this->tokenService->createCustomerAccessToken($customerUserName, $password);
        $customerData = $this->accountManagement->authenticate($customerUserName, $password);
        /** @var $token TokenModel */
        $token = $this->tokenModel->loadByCustomerId($customerData->getId())->getToken();
        $this->assertEquals($accessToken, $token);
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testCreateCustomerAccessTokenEmptyOrNullCredentials($username, $password)
    {
        try {
            $this->tokenService->createCustomerAccessToken($username, $password);
        } catch (InputException $e) {
            $this->assertInputExceptionMessages($e);
        }
    }

    /**
     * @expectedException \Magento\Framework\Exception\InvalidEmailOrPasswordException
     * @expectedExceptionMessage Invalid login or password.
     */
    public function testCreateCustomerAccessTokenInvalidCustomer()
    {
        $customerUserName = 'invalid';
        $password = 'invalid';
        $this->tokenService->createCustomerAccessToken($customerUserName, $password);
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
        $this->assertEquals(InputException::DEFAULT_MESSAGE, $e->getMessage());
        $errors = $e->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('username is a required field.', $errors[0]->getLogMessage());
        $this->assertEquals('password is a required field.', $errors[1]->getLogMessage());
    }
}
