<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Test\Unit\Model;

use Magento\CardinalCommerce\Model\JwtManagement;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

/**
 * Tests JWT encode and decode.
 */
class JwtManagementTest extends TestCase
{
    /**
     * API key
     */
    private const API_KEY = 'API key';

    /**
     * @var JwtManagement
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new JwtManagement(new Json());
    }

    /**
     * Tests JWT encode.
     */
    public function testEncode()
    {
        $jwt = $this->model->encode($this->getTokenPayload(), self::API_KEY);

        $this->assertEquals(
            $this->getValidHS256Jwt(),
            $jwt,
            'Generated JWT isn\'t equal to expected'
        );
    }

    /**
     * Tests JWT decode.
     */
    public function testDecode()
    {
        $tokenPayload = $this->model->decode($this->getValidHS256Jwt(), self::API_KEY);

        $this->assertEquals(
            $this->getTokenPayload(),
            $tokenPayload,
            'JWT payload isn\'t equal to expected'
        );
    }

    /**
     * Tests JWT decode.
     *
     * @param string $jwt
     * @param string $errorMessage
     * @dataProvider decodeWithExceptionDataProvider
     */
    public function testDecodeWithException(string $jwt, string $errorMessage)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->model->decode($jwt, self::API_KEY);
    }

    /**
     * @return array
     */
    public function decodeWithExceptionDataProvider(): array
    {
        return [
            [
                'jwt' => '',
                'errorMessage' => 'JWT is empty',
            ],
            [
                'jwt' => 'dddd.dddd',
                'errorMessage' => 'Wrong number of segments in JWT',
            ],
            [
                'jwt' => 'dddd.dddd.dddd',
                'errorMessage' => 'Unable to unserialize value. Error: Syntax error',
            ],
            [
                'jwt' => $this->getHS512Jwt(),
                'errorMessage' => 'Algorithm HS512 is not supported',
            ],
            [
                'jwt' => $this->getJwtWithInvalidSignature(),
                'errorMessage' => 'JWT signature verification failed',
            ],
        ];
    }

    /**
     * Returns valid JWT, signed using HS256.
     *
     * @return string
     */
    private function getValidHS256Jwt(): string
    {
        return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJhNWE1OWJmYi1hYzA2LTRjNWYtYmU1Yy0zNTFiNjR' .
            'hZTYwOGUiLCJpc3MiOiI1NjU2MGEzNThiOTQ2ZTBjODQ1MjM2NWRzIiwiaWF0IjoiMTQ0ODk5Nzg2NSIsIk9yZ1Vua' .
            'XRJZCI6IjU2NTYwN2MxOGI5NDZlMDU4NDYzZHM4ciIsIlBheWxvYWQiOnsiT3JkZXJEZXRhaWxzIjp7Ik9yZGVyTnV' .
            'tYmVyIjoiMTI1IiwiQW1vdW50IjoiMTUwMCIsIkN1cnJlbmN5Q29kZSI6IlVTRCJ9fSwiT2JqZWN0aWZ5UGF5bG9hZ' .
            'CI6dHJ1ZX0.emv9N75JIvyk_gQHMNJYQ2UzmbM5ISBQs1Y222zO1jk';
    }

    /**
     * Returns JWT, signed using not supported HS512.
     *
     * @return string
     */
    private function getHS512Jwt(): string
    {
        return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJqdGkiOiJhNWE1OWJmYi1hYzA2LTRjNWYtYmU1Yy0zNTFiNjR' .
               'hZTYwOGUiLCJpc3MiOiI1NjU2MGEzNThiOTQ2ZTBjODQ1MjM2NWRzIiwiaWF0IjoiMTQ0ODk5Nzg2NSIsIk9yZ1V' .
               'uaXRJZCI6IjU2NTYwN2MxOGI5NDZlMDU4NDYzZHM4ciIsIlBheWxvYWQiOnsiT3JkZXJEZXRhaWxzIjp7Ik9yZGV' .
               'yTnVtYmVyIjoiMTI1IiwiQW1vdW50IjoiMTUwMCIsIkN1cnJlbmN5Q29kZSI6IlVTRCJ9fSwiT2JqZWN0aWZ5UGF' .
               '5bG9hZCI6dHJ1ZX0.4fwdXfIgUe7bAiHP2bZsxSG-s-wJOyaCmCe9MOQhs-m6LLjRGarguA_0SqZA2EeUaytMO4R' .
               'G84ZEOfbYfS8c1A';
    }

    /**
     * Returns JWT with invalid signature.
     *
     * @return string
     */
    private function getJwtWithInvalidSignature(): string
    {
        return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJhNWE1OWJmYi1hYzA2LTRjNWYtYmU1Yy0zNTFiNjR' .
            'hZTYwOGUiLCJpc3MiOiI1NjU2MGEzNThiOTQ2ZTBjODQ1MjM2NWRzIiwiaWF0IjoiMTQ0ODk5Nzg2NSIsIk9yZ1Vua' .
            'XRJZCI6IjU2NTYwN2MxOGI5NDZlMDU4NDYzZHM4ciIsIlBheWxvYWQiOnsiT3JkZXJEZXRhaWxzIjp7Ik9yZGVyTnV' .
            'tYmVyIjoiMTI1IiwiQW1vdW50IjoiMTUwMCIsIkN1cnJlbmN5Q29kZSI6IlVTRCJ9fSwiT2JqZWN0aWZ5UGF5bG9hZ' .
            'CI6dHJ1ZX0.InvalidSign';
    }

    /**
     * Returns token decoded payload.
     *
     * @return array
     */
    private function getTokenPayload(): array
    {
        return [
            'jti' => 'a5a59bfb-ac06-4c5f-be5c-351b64ae608e',
            'iss' => '56560a358b946e0c8452365ds',
            'iat' => '1448997865',
            'OrgUnitId' => '565607c18b946e058463ds8r',
            'Payload' => [
                'OrderDetails' => [
                    'OrderNumber' => '125',
                    'Amount' => '1500',
                    'CurrencyCode' => 'USD'
                ]
            ],
            'ObjectifyPayload' => true
        ];
    }
}
