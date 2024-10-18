<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\Framework\Jwt\Header\PrivateHeaderParameter;
use Magento\Framework\Jwt\Jwe\JweEncryptionSettingsInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\JwtUserToken\Api\ConfigReaderInterface;
use Magento\JwtUserToken\Api\Data\JwtTokenDataInterface;
use Magento\JwtUserToken\Model\Config\ConfigReader;
use Magento\JwtUserToken\Model\Data\JwtTokenParameters;
use Magento\JwtUserToken\Model\Data\JwtUserContext;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\User\Model\User as UserModel;
use PHPUnit\Framework\TestCase;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Api\Data\UserTokenParametersInterfaceFactory;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    private $model;

    /**
     * @var Issuer
     */
    private $issuer;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * @var UserTokenParametersInterfaceFactory
     */
    private $paramsFactory;

    /**
     * @var MutableScopeConfigInterface
     */
    private $config;

    public static function getJwtCases(): array
    {
        return [
            'jws-hs256' => [Jwk::ALGORITHM_HS256, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM],
            'jws-hs384' => [Jwk::ALGORITHM_HS384, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM],
            'jwe-a128kw-a128gcm' => [
                Jwk::ALGORITHM_A128KW,
                JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
            ],
            'jwe-a256gcmkw-a192hs384' => [
                Jwk::ALGORITHM_A256GCMKW,
                JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192_HS384
            ],
        ];
    }

    /**
     * Verify that a JWT token can be issued for a customer using various algorithms.
     *
     * @param string $jwtAlg JWT algorithm to use.
     * @param string $jweAlg JWE content encryption algorithm.
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider getJwtCases
     */
    public function testIssueForCustomer(string $jwtAlg, string $jweAlg): void
    {
        $this->config->setValue('webapi/jwtauth/jwt_alg', $jwtAlg);
        $this->config->setValue('webapi/jwtauth/jwe_alg', $jweAlg);

        $customer = $this->customerRepo->get('customer@example.com');
        /** @var UserTokenParametersInterface $params */
        $params = $this->paramsFactory->create();
        $jwtParams = new JwtTokenParameters();
        $jwtParams->setClaims([new PrivateClaim($claim = 'custom-claim', $claimValue = 'value')]);
        $jwtParams->setProtectedHeaderParameters(
            [new PrivateHeaderParameter($header = 'custom-header', $headerValue = 42)]
        );
        $params->getExtensionAttributes()->setJwtParams($jwtParams);
        $token = $this->issuer->create(
            new JwtUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER),
            $params
        );

        $data = $this->model->read($token);
        $this->assertInstanceOf(JwtTokenDataInterface::class, $data->getData());
        /** @var JwtTokenDataInterface $tokenData */
        $tokenData = $data->getData();
        $this->assertEquals(UserContextInterface::USER_TYPE_CUSTOMER, $data->getUserContext()->getUserType());
        $this->assertEquals((int) $customer->getId(), $data->getUserContext()->getUserId());
        $this->assertGreaterThan($tokenData->getIssued(), $tokenData->getExpires());
        $claims = [];
        foreach ($tokenData->getJwtClaims()->getClaims() as $item) {
            $claims[$item->getName()] = $item;
        }
        $this->assertArrayHasKey($claim, $claims);
        $this->assertEquals($claimValue, $claims[$claim]->getValue());
        $headerFound = $tokenData->getJwtHeader()->getParameter($header);
        $this->assertNotNull($headerFound);
        $this->assertEquals($headerValue, $headerFound->getValue());
        $this->assertEquals($jwtAlg, $tokenData->getJwtHeader()->getParameter('alg')->getValue());
        if ($enc = $tokenData->getJwtHeader()->getParameter('enc')) {
            $this->assertEquals($jweAlg, $enc->getValue());
        }
    }

    /**
     * Verify that a token can be issued for an admin user.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testIssueForAdminCases(): void
    {
        $admin = $this->userModel->loadByUsername('adminUser');
        /** @var UserTokenParametersInterface $params */
        $params = $this->paramsFactory->create();
        $token = $this->issuer->create(
            new JwtUserContext((int) $admin->getId(), UserContextInterface::USER_TYPE_ADMIN),
            $params
        );

        $data = $this->model->read($token);
        $this->assertInstanceOf(JwtTokenDataInterface::class, $data->getData());
        /** @var JwtTokenDataInterface $tokenData */
        $this->assertEquals(UserContextInterface::USER_TYPE_ADMIN, $data->getUserContext()->getUserType());
        $this->assertEquals((int) $admin->getId(), $data->getUserContext()->getUserId());
        $this->assertGreaterThan($data->getData()->getIssued(), $data->getData()->getExpires());
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(Reader::class);
        $this->issuer = $objectManager->get(Issuer::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->userModel = $objectManager->create(UserModel::class);
        $this->paramsFactory = $objectManager->get(UserTokenParametersInterfaceFactory::class);
        $this->config = $objectManager->get(MutableScopeConfigInterface::class);
    }
}
