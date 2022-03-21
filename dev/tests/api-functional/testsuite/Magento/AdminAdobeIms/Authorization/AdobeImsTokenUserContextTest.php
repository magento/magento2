<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Authorization;

use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\Framework\Jwt\Header\PrivateHeaderParameter;
use Magento\Framework\Jwt\JwkFactory;
use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Model\User;

/**
 * Checks the categories/list api
 */
class AdobeImsTokenUserContextTest  extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/store/storeConfigs';
    const SERVICE_NAME = 'storeStoreConfigManagerV1';
    const SERVICE_VERSION = 'V1';
    const KEYS_LOCATION = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
    const TEST_ADOBE_USER_ID = '121B46F2620BF4240A49TEST@AdobeID';

    /**
     * @var JwtManagerInterface
     */
    private $manager;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var UserProfileRepositoryInterface
     */
    private $userProfileRepository;
    /**
     * @var User
     */
    private $userModel;
    /**
     * @var JwkFactory
     */
    private $jwkFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->manager = $objectManager->get(JwtManagerInterface::class);
        $this->cache = $objectManager->get(CacheInterface::class);
        $this->userProfileRepository = $objectManager->get(UserProfileRepositoryInterface::class);
        $this->userModel = $objectManager->get(User::class);
        $this->jwkFactory = $objectManager->get(JwkFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testUseAdobeAccessTokenSuccess()
    {
        $adminUserNameFromFixture = 'webapi_user';
        $token = $this->createAccessToken();
        $this->runWebApiCall($token);
        $this->assertAdobeUserIsSaved($adminUserNameFromFixture);
    }

    public function testUseAdobeAccessTokenAdminNotExist()
    {
        $token = $this->createAccessToken();
        $noExceptionOccurred = false;
        $expectedMessage = 'The consumer isn\'t authorized to access %resources.';
        try {
            $this->runWebApiCall($token);
            $noExceptionOccurred = true;
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Exception $exception) {
            $exceptionData = $this->processRestExceptionResult($exception);
            $this->assertEquals($expectedMessage, $exceptionData['message']);
            $this->assertEquals(['resources' => 'Magento_Backend::store'], $exceptionData['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $exception->getCode());
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when admin user does not exist.");
        }
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testUseAdobeAccessTokenExpired()
    {
        $token = $this->createAccessToken('-2 day');

        $noExceptionOccurred = false;
        $expectedMessage = "Token has expired";
        try {
            $this->runWebApiCall($token);
            $noExceptionOccurred = true;
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Exception $exception) {
            $exceptionData = $this->processRestExceptionResult($exception);
            $expectedExceptionData = ['message' => $expectedMessage];
            $this->assertEquals($expectedExceptionData, $exceptionData);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided token is expired.");
        }
    }

    private function runWebApiCall($token)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getStoreConfigs',
                'token' => $token
            ],
        ];

        $requestData = [
            'storeCodes' => ['default'],
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @return string
     */
    private function createAccessToken($createdAt = 'now'): string
    {
        $this->cache->save(
            file_get_contents(self::KEYS_LOCATION . 'jwtRS256.key.pub'),
            'AdminAdobeIms_jwtRS256.key.pub'
        );

        $jwsJwk = $this->jwkFactory->createSignRs256(
            file_get_contents(self::KEYS_LOCATION . 'jwtRS256.key'),
            null
        );

        $jwsSettings = new JwsSignatureJwks($jwsJwk);

        // timestamp in milliseconds
        $date = (new \DateTime($createdAt))->getTimestamp() * 1000;

        return $this->manager->create(new Jws(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('alg', 'RS256'),
                        new PrivateHeaderParameter('x5u', 'jwtRS256.key.pub'),
                        new PrivateHeaderParameter('kid', 'jwtRS256.key'),
                        new PrivateHeaderParameter('itt', 'at')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('user_id', self::TEST_ADOBE_USER_ID),
                    new PrivateClaim('created_at',  $date),
                    new PrivateClaim('expires_in',  '86400000')
                ]
            ),
            null
        ), $jwsSettings);
    }

    /**
     * Check if adobe_user_id was saved into adobe_user_profile table
     *
     * @param string $accessToken
     * @param string $userName
     * @param string $password
     */
    private function assertAdobeUserIsSaved($username)
    {
        $adminUserId = (int) $this->userModel->loadByUsername($username)->getId();
        $userProfile = $this->userProfileRepository->getByUserId($adminUserId);
        if ($userProfile->getId()) {
            $this->assertEquals(self::TEST_ADOBE_USER_ID, $userProfile->getData('adobe_user_id'));
        }
    }

}

