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
    public function getConstructorCases(): array
    {
        return [
            'valid-jwk' => [$this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE), true],
            'valid-jwks' => [
                $this->createJwkSet(
                    [
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE),
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE)
                    ]
                ),
                true
            ],
            'invalid-jwk' => [$this->createJwk(Jwk::PUBLIC_KEY_USE_ENCRYPTION), false],
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

        new JwsSignatureJwks($jwks);
    }

    public function getAlgorithmCases(): array
    {
        return [
            'one-algo' => [
                $this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS384),
                Jwk::ALGORITHM_HS384
            ],
            'json' => [
                $this->createJwkSet(
                    [
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE),
                        $this->createJwk(Jwk::PUBLIC_KEY_USE_SIGNATURE)
                    ]
                ),
                'jws-json-serialization'
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
