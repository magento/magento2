<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Authorization;

use Exception;
use Magento\AdminAdobeIms\Api\ImsWebapiRepositoryInterface;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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
 * Runs the storeConfigs api to check provided token
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdobeImsTokenUserContextTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/store/storeConfigs';
    private const SERVICE_NAME = 'storeStoreConfigManagerV1';
    private const SERVICE_VERSION = 'V1';
    private const KEYS_LOCATION = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
    private const TEST_ADOBE_USER_ID = '121B46F2620BF4240A49TEST@AdobeID';

    /**
     * @var JwtManagerInterface
     */
    private $manager;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var User
     */
    private $userModel;

    /**
     * @var JwkFactory
     */
    private $jwkFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ImsWebapiRepositoryInterface
     */
    private $imsWebapiRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->manager = $objectManager->get(JwtManagerInterface::class);
        $this->cache = $objectManager->get(CacheInterface::class);
        $this->userModel = $objectManager->get(User::class);
        $this->jwkFactory = $objectManager->get(JwkFactory::class);
        $this->configWriter = $objectManager->get(WriterInterface::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->imsWebapiRepository = $objectManager->get(ImsWebapiRepositoryInterface::class);
        $this->encryptor = $objectManager->get(EncryptorInterface::class);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testUseAdobeAccessTokenModuleDisabled()
    {
        $this->configWriter->save(ImsConfig::XML_PATH_ENABLED, 0);
        $this->scopeConfig->clean();

        $token = $this->createAccessToken();
        $noExceptionOccurred = false;
        try {
            $this->runWebApiCall($token);
            $noExceptionOccurred = true;
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                'The consumer isn\'t authorized to access %resources.',
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (Exception $exception) {
            $this->assertUnauthorizedAccessException($exception);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when admin user does not exist.");
        }
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     * @throws Exception
     */
    public function testUseAdobeAccessTokenSuccess()
    {
        $adminUserNameFromFixture = 'webapi_user';
        $token = $this->createAccessToken();
        $this->configWriter->save(ImsConfig::XML_PATH_ENABLED, 1);
        $this->scopeConfig->clean();
        $this->runWebApiCall($token);
        $this->assertAdminUserIdIsSaved($adminUserNameFromFixture, $token);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testUseAdobeAccessTokenAdminNotExist()
    {
        $token = $this->createAccessToken();
        $noExceptionOccurred = false;
        try {
            $this->runWebApiCall($token);
            $noExceptionOccurred = true;
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                'The consumer isn\'t authorized to access %resources.',
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (Exception $exception) {
            $this->assertUnauthorizedAccessException($exception);
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
        } catch (Exception $exception) {
            $exceptionData = $this->processRestExceptionResult($exception);
            $expectedExceptionData = ['message' => $expectedMessage];
            $this->assertEquals($expectedExceptionData, $exceptionData);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided token is expired.");
        }
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testUseAdobeAccessTokenInvalidCertificate()
    {
        $token = $this->createAccessToken('now', true);

        $noExceptionOccurred = false;
        try {
            $this->runWebApiCall($token);
            $noExceptionOccurred = true;
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                'The consumer isn\'t authorized to access %resources.',
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (Exception $exception) {
            $this->assertUnauthorizedAccessException($exception);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided token has invalid certificate.");
        }
    }

    /**
     * @param string $token
     * @return void
     */
    private function runWebApiCall(string $token)
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
     * @param string $createdAt
     * @param bool $isCertificateInvalid
     * @return string
     * @throws Exception
     */
    private function createAccessToken($createdAt = 'now', $isCertificateInvalid = false): string
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

        $x5u = $isCertificateInvalid ? 'invalid certificate' : 'jwtRS256.key.pub';
        return $this->manager->create(new Jws(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('alg', 'RS256'),
                        new PrivateHeaderParameter('x5u', $x5u),
                        new PrivateHeaderParameter('kid', 'jwtRS256.key'),
                        new PrivateHeaderParameter('itt', 'at')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('user_id', self::TEST_ADOBE_USER_ID),
                    new PrivateClaim('created_at', $date),
                    new PrivateClaim('expires_in', '86400000')
                ]
            ),
            null
        ), $jwsSettings);
    }

    /**
     * Check if admin_user_id was saved into admin_adobe_ims_webapi table
     *
     * @param string $username
     * @param string $token
     * @throws NoSuchEntityException
     */
    private function assertAdminUserIdIsSaved(string $username, string $token)
    {
        $adminUserId = (int) $this->userModel->loadByUsername($username)->getId();
        $webapiEntity = $this->imsWebapiRepository->getByAccessTokenHash($this->encryptor->getHash($token));
        if ($webapiEntity->getId()) {
            $this->assertEquals($adminUserId, $webapiEntity->getAdminUserId());
        }
    }

    /**
     * Make sure that status code and message are correct in case of authentication failure.
     *
     * @param Exception $exception
     */
    private function assertUnauthorizedAccessException($exception)
    {
        $expectedMessage = 'The consumer isn\'t authorized to access %resources.';
        $exceptionData = $this->processRestExceptionResult($exception);
        $this->assertEquals($expectedMessage, $exceptionData['message']);
        $this->assertEquals(['resources' => 'Magento_Backend::store'], $exceptionData['parameters']);
        $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $exception->getCode());
    }
}
