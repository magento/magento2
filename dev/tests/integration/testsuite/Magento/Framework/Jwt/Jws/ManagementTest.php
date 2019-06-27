<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Core\Algorithm;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS512;
use Magento\Framework\Jwt\AlgorithmFactory;
use Magento\Framework\Jwt\ClaimCheckerManager;
use Magento\Framework\Jwt\KeyGenerator\StringKey;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Contains testing scenarios for JWS encoding/decoding/verification.
 */
class ManagementTest extends TestCase
{
    private const KEY = 'uYLQom8onamDSF6HK9RE3BQXypO3kZedd3j9H6g6kGPvzQDBgsqR9gVTGyRyLFFX';

    /**
     * @var Management
     */
    private $management;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StringKey
     */
    private $keyGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->keyGenerator = $this->objectManager->create(StringKey::class, ['key' => self::KEY]);
        $this->management = $this->objectManager->create(Management::class, ['keyGenerator' => $this->keyGenerator]);
    }

    /**
     * Tests JWT creation based on HMAC SHA256
     */
    public function testEncodeHs256(): void
    {
        $claims = [
            'iss' => 'Magento',
            'iat' => 1561564372,
            'exp' => 1593100372,
            'aud' => 'dev',
            'sub' => 'test',
            'key' => 'value'
        ];

        $encoded = $this->management->encode($claims);
        self::assertEquals($this->getSh256Jwt(), $encoded, 'The signatures do not match.');
    }

    /**
     * Tests JWT creation based on HMAC SHA512
     */
    public function testEncodeHs512(): void
    {
        $claims = [
            'iss' => 'Magento',
            'iat' => 1561564372,
            'exp' => 1593100372,
            'aud' => 'dev',
            'sub' => 'test',
            'key' => 'value'
        ];

        $algorithmFactory = $this->objectManager->create(AlgorithmFactory::class, ['algorithm' => new HS512()]);
        /** @var Management $management */
        $management = $this->objectManager->create(
            Management::class,
            [
                'keyGenerator' => $this->keyGenerator,
                'algorithmFactory' => $algorithmFactory,
            ]
        );

        $encoded = $management->encode($claims);
        self::assertEquals($this->getSh512Jwt(), $encoded, 'The signatures do not match.');
    }

    /**
     * Tests encoding with invalid key for signature generation.
     *
     * @param string $key
     * @param Algorithm $algorithm
     * @dataProvider invalidKeyDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid key length.
     */
    public function testEncodeWithInvalidKey(string $key, Algorithm $algorithm): void
    {
        $algorithmFactory = $this->objectManager->create(AlgorithmFactory::class, ['algorithm' => $algorithm]);
        $keyGenerator = $this->objectManager->create(StringKey::class, ['key' => $key]);
        /** @var Management $management */
        $management = $this->objectManager->create(
            Management::class,
            [
                'keyGenerator' => $keyGenerator,
                'algorithmFactory' => $algorithmFactory,
            ]
        );

        $management->encode([]);
    }

    /**
     * Get a list of invalid keys and different algorithms.
     *
     * @return array
     */
    public function invalidKeyDataProvider(): array
    {
        return [
            [
                'key' => 'vqrnGmwCWtTdEOXVny4NPl1fLccaQ4a', // 31 symbol
                'algorithm' => new HS256()
            ],
            [
                'key' => '0ou9ndCTIqQc43F0b88DJLL8QTiTRbitItKiolFNG3e9FFCRcwRMqz4WMUT7wdw', // 63 symbols
                'algorithm' => new HS512()
            ],
        ];
    }

    /**
     * Tests JWT decoding
     */
    public function testDecode(): void
    {
        $expected = [
            'iss' => 'Magento',
            'iat' => 1561564372,
            'exp' => 1593100372,
            'aud' => 'dev',
            'sub' => 'test',
            'key' => 'value'
        ];

        $jwt = $this->getSh256Jwt();
        $payload = $this->management->decode($jwt);
        self::assertEquals($expected, $payload);
    }

    /**
     * Tests JWT verification
     *
     * @param string $token
     * @param string $key
     * @param bool $expected
     * @dataProvider tokenVerificationDataProvider
     */
    public function testVerify(string $token, string $key, bool $expected): void
    {
        $claimCheckerManager = $this->objectManager->create(
            ClaimCheckerManager::class,
            [
                'checkers' => [
                    ExpirationTimeChecker::class,
                    IssuedAtChecker::class
                ]
            ]
        );
        $keyGenerator = $this->objectManager->create(StringKey::class, ['key' => $key]);
        /** @var Management $management */
        $management = $this->objectManager->create(
            Management::class,
            [
                'keyGenerator' => $keyGenerator,
                'claimCheckerManager' => $claimCheckerManager
            ]
        );
        $result = $management->verify($token, ['iat', 'exp']);
        self::assertEquals($expected, $result, 'Mismatched verification result.');
    }

    /**
     * Get a list of variations for JWT verification.
     *
     * @return array
     */
    public function tokenVerificationDataProvider(): array
    {
        return [
            [
                'token' => $this->getSh256Jwt(),
                'key' => self::KEY,
                'expected' => true
            ],
            [
                'token' => $this->getSh256Jwt(),
                'key' => 's11TrujqWuoYyYiRWWHldMKunGW5cQGv',
                'expected' => false
            ],
            [
                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJNYWdlbnRvIiwiaWF0IjoxNTYxNTY0MzcyLCJleH' .
                    'AiOjE1OTMxMDAzNzIsImF1ZCI6ImRldiIsInN1YiI6InRlc3QiLCJrZXkiOiJ2YWx1ZSJ9.J0zj5NyntWBZfit7mG00O7G' .
                    '1oN91Dzc3m12rKv1o',
                'key' => self::KEY,
                'expected' => false
            ],
        ];
    }

    /**
     * Gets SHA256 JWT.
     *
     * @return string
     */
    private function getSh256Jwt(): string
    {
        return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJkZXYiLCJleHAiOjE1OTMxMDAzNzIsImlhdCI6MTU2MTU2NDM3M' .
            'iwiaXNzIjoiTWFnZW50byIsImtleSI6InZhbHVlIiwic3ViIjoidGVzdCJ9.VcFSXAxjzjo3isx9wsRZwtMzfKQHeCkd9n6KAJTdFxQ';
    }

    /**
     * Gets SHA512 JWT.
     *
     * @return string
     */
    private function getSh512Jwt(): string
    {
        return 'eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJkZXYiLCJleHAiOjE1OTMxMDAzNzIsImlhdCI6MTU2MTU2NDM3M' .
            'iwiaXNzIjoiTWFnZW50byIsImtleSI6InZhbHVlIiwic3ViIjoidGVzdCJ9.p4clm9yU8O_Dk7HnAEKC3cRmhB8AZN-cXBtaKaOho' .
            'bLqgBp-uO0fUdNjnLrQivviY3gN99cbJDMNQxb-lN0TVA';
    }
}
