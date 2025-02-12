<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Test\Unit\Jws;

use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkSet;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use PHPUnit\Framework\TestCase;

class JwsSignatureJwksTest extends TestCase
{
    public static function getConstructorCases(): array
    {
        return [
            'valid-jwk' => [Jwk::PUBLIC_KEY_USE_SIGNATURE, true],
            'valid-jwks' => [
                [Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::PUBLIC_KEY_USE_SIGNATURE],
                true
            ],
            'invalid-jwk' => [Jwk::PUBLIC_KEY_USE_ENCRYPTION, false],
            'invalid-jwks' => [
                [Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::PUBLIC_KEY_USE_ENCRYPTION],
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
        if (is_array($jwks)) {
            $jwks = array_map(function ($keyUse) {
                return $this->createJwk($keyUse);
            }, $jwks);
            $jwks = $this->createJwkSet($jwks);
        } else {
            $jwks = $this->createJwk($jwks);
        }

        if (!$valid) {
            $this->expectException(EncryptionException::class);
        }

        new JwsSignatureJwks($jwks);
    }

    public static function getAlgorithmCases(): array
    {
        return [
            'one-algo' => [
                [Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS384],
                Jwk::ALGORITHM_HS384
            ],
            'json' => [
                [
                    [Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS256],
                    [Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS256]
                ],
                'jws-json-serialization'
            ]
        ];
    }

    /**
     * Test algorithm logic.
     *
     * @param array $jwksData
     * @param string $expectedName
     * @return void
     * @dataProvider getAlgorithmCases
     */
    public function testGetAlgorithmName(array $jwkData, string $expectedName): void
    {
        if (is_array($jwkData[0])) {
            $jwks = array_map(function ($data) {
                return $this->createJwk($data[0], $data[1]);
            }, $jwkData);
            $jwk = $this->createJwkSet($jwks);
        } else {
            $jwk = $this->createJwk($jwkData[0], $jwkData[1]);
        }

        $model = new JwsSignatureJwks($jwk);

        $this->assertEquals($expectedName, $model->getAlgorithmName());
    }

    private function createJwk(string $use, string $alg = Jwk::ALGORITHM_HS256): Jwk
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
