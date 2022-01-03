<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Test\Unit\Jwe;

use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Jwe\JweEncryptionJwks;
use Magento\Framework\Jwt\Jwe\JweEncryptionSettingsInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkSet;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use PHPUnit\Framework\TestCase;

class JweEncryptionJwksTest extends TestCase
{
    public function getConstructorCases(): array
    {
        return [
            'valid-jwk' => [$this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION), true],
            'valid-jwks' => [
                $this->createJwkSet(
                    [
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION),
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION)
                    ]
                ),
                true
            ],
            'invalid-jwk' => [$this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE), false],
            'invalid-jwks' => [
                $this->createJwkSet(
                    [
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE),
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION)
                    ]
                ),
                false
            ]
        ];
    }

    /**
     * Test constructor validation.
     *
     * @param Jwk|JwkSet $jwks
     * @param bool $valid
     * @return void
     * @dataProvider getConstructorCases
     */
    public function testConstruct($jwks, $valid): void
    {
        if (!$valid) {
            $this->expectException(EncryptionException::class);
        }

        new JweEncryptionJwks($jwks, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM);
    }

    public function getAlgorithmCases(): array
    {
        return [
            'one-algo' => [
                $this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_RSA_OAEP),
                Jwk::ALGORITHM_RSA_OAEP
            ],
            'json' => [
                $this->createJwkSet(
                    [
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION),
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION)
                    ]
                ),
                'jwe-json-serialization'
            ],
        ];
    }

    /**
     * Test algorithm logic.
     *
     * @param Jwk|JwkSet $jwk
     * @param string $expectedName
     * @return void
     * @dataProvider getAlgorithmCases
     */
    public function testGetAlgorithmName($jwk, string $expectedName): void
    {
        $model = new JweEncryptionJwks($jwk, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM);

        $this->assertEquals($expectedName, $model->getAlgorithmName());
    }

    private function createJwk(string $use, string $alg = Jwk::ALGORITHM_RSA_OAEP_256): Jwk
    {
        $mock = $this->createMock(Jwk::class);
        $mock->method('getPublicKeyUse')->willReturn($use);
        $mock->method('getAlgorithm')->willReturn($alg);

        return $mock;
    }

    public function createJwkSet(array $jwks): JwkSet
    {
        $mock = $this->createMock(JwkSet::class);
        $mock->method('getKeys')->willReturn($jwks);

        return $mock;
    }
}
