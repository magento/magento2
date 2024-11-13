<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\JwtFrameworkAdapter\Model;

class JwsManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        parent::setUp();
    }

    public function testCreatingJwsWithAlgorithmSetInHeadersDirectly(): void
    {
        $secret = "ZXF1YXRpb24tS2VudHVja3ktY29udGludWVkLWRpZmZlcmVuY2U";
        $payload = json_encode([
            'MyCustomClaim' => 'some value', // not important at all
            'nbf' => time(),
            'exp' => time() + 600,
            'iat' => time()
        ]);
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        /** @var \Magento\Framework\Jwt\JwkFactory $jwkFactory */
        $jwkFactory = $this->objectManager->create(\Magento\Framework\Jwt\JwkFactory::class);
        $jwk = $jwkFactory->createFromData(['kty' => 'oct', 'k' => $secret]);

        /** @var \Magento\JwtFrameworkAdapter\Model\JwsFactory $jwsFactory */
        $jwsFactory = $this->objectManager->create(\Magento\JwtFrameworkAdapter\Model\JwsFactory::class);
        $jws = $jwsFactory->create($header, $payload, null);

        /** @var \Magento\Framework\Jwt\Jws\JwsSignatureSettingsInterface $encryptionSettings */
        $encryptionSettings = $this->objectManager->create(
            \Magento\Framework\Jwt\Jws\JwsSignatureJwks::class,
            [
                'jwk' => $jwk
            ]
        );

        /** @var \Magento\JwtFrameworkAdapter\Model\JwsManager $jwsManager */
        $jwsManager = $this->objectManager->create(\Magento\JwtFrameworkAdapter\Model\JwsManager::class);

        $token = $jwsManager->build($jws, $encryptionSettings);

        $this->assertIsString($token);
    }
}
