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
    public static function getConstructorCases(): array
    {
        return [
            'valid-jwk' => [['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION], true],
            'valid-jwks' => [
                [
                    [
                        ['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION],
                        ['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION]
                    ]
                ],
                true
            ],
            'invalid-jwk' => [['use' => Jwk::PUBLIC_KEY_USE_SIGNATURE], false],
            'invalid-jwks' => [
                [
                    [
                        ['use' => Jwk::PUBLIC_KEY_USE_SIGNATURE],
                        ['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION]
                    ]
                ],
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
    public function testConstruct(array $jwkData, bool $valid): void
    {
        if (isset($jwkData[0]) && is_array($jwkData[0])) {
            $jwks = array_map(function ($data) {
                return $this->createJwk($data['use']);
            }, $jwkData[0]);
            $jwkSet = $this->createJwkSet($jwks);
            $jwksObject = $jwkSet;
        } else {
            $jwksObject = $this->createJwk($jwkData['use']);
        }

        if (!$valid) {
            $this->expectException(EncryptionException::class);
        }

        new JweEncryptionJwks($jwksObject, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM);
    }

    public static function getAlgorithmCases(): array
    {
        return [
            'one-algo' => [
                ['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION, 'alg' => Jwk::ALGORITHM_RSA_OAEP],
                Jwk::ALGORITHM_RSA_OAEP
            ],
            'json' => [
                [
                    [
                        ['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION, 'alg' => Jwk::ALGORITHM_RSA_OAEP],
                        ['use' => Jwk::PUBLIC_KEY_USE_ENCRYPTION, 'alg' => Jwk::ALGORITHM_RSA_OAEP]
                    ]
                ],
                'jwe-json-serialization'
            ],
        ];
    }

    /**
     * Test algorithm logic.
     *
     * @param array $jwkData
     * @param string $expectedName
     * @return void
     * @dataProvider getAlgorithmCases
     */
    public function testGetAlgorithmName(array $jwkData, string $expectedName): void
    {
        if (isset($jwkData['use'])) {
            $jwk = $this->createJwk($jwkData['use'], $jwkData['alg']);
            $model = new JweEncryptionJwks($jwk, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM);
        } else {
            $jwks = array_map(function ($data) {
                return $this->createJwk($data['use'], $data['alg']);
            }, $jwkData[0]);
            $jwkSet = $this->createJwkSet($jwks);
            $model = new JweEncryptionJwks($jwkSet, JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM);
        }

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
