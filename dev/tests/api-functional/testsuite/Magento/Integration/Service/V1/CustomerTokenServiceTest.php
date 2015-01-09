<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Integration\Service\V1;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Model\User as UserModel;
use Magento\Webapi\Exception as HTTPExceptionCodes;

/**
 * api-functional test for \Magento\Integration\Service\V1\CustomerTokenService.
 */
class CustomerTokenServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = "integrationCustomerTokenServiceV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";
    const RESOURCE_PATH_ADMIN_TOKEN = "/V1/integration/admin/token";

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * Setup CustomerTokenService
     */
    public function setUp()
    {
        $this->_markTestAsRestOnly();
        $this->tokenService = Bootstrap::getObjectManager()->get('Magento\Integration\Service\V1\CustomerTokenService');
        $this->customerAccountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $this->tokenModel = Bootstrap::getObjectManager()->get('Magento\Integration\Model\Oauth\Token');
        $this->userModel = Bootstrap::getObjectManager()->get('Magento\User\Model\User');
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateCustomerAccessToken()
    {
        $customerUserName = 'customer@example.com';
        $password = 'password';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['username' => $customerUserName, 'password' => $password];
        $accessToken = $this->_webApiCall($serviceInfo, $requestData);

        $customerData = $this->customerAccountManagement->authenticate($customerUserName, $password);
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
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                    'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => '', 'password' => ''];
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            $this->assertInputExceptionMessages($e);
        }
    }

    public function testCreateCustomerAccessTokenInvalidCustomer()
    {
        $customerUserName = 'invalid';
        $password = 'invalid';
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                    'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => $customerUserName, 'password' => $password];
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $e->getCode());
            $exceptionData = $this->processRestExceptionResult($e);
            $expectedExceptionData = ['message' => 'Invalid login or password.'];
        }
        $this->assertEquals($expectedExceptionData, $exceptionData);
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
     * @param \Exception $e
     */
    private function assertInputExceptionMessages($e)
    {
        $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        $exceptionData = $this->processRestExceptionResult($e);
        $expectedExceptionData = [
            'message' => InputException::DEFAULT_MESSAGE,
            'errors' => [
                [
                    'message' => InputException::REQUIRED_FIELD,
                    'parameters' => [
                        'fieldName' => 'username',
                    ],
                ],
                [
                    'message' => InputException::REQUIRED_FIELD,
                    'parameters' => [
                        'fieldName' => 'password',
                    ]
                ],
            ],
        ];
        $this->assertEquals($expectedExceptionData, $exceptionData);
    }
}
