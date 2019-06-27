<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests JWT encode and decode.
 */
class JwtManagementTest extends TestCase
{
    /**
     * @var JwtManagement
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(JwtManagement::class);
    }

    /**
     * Tests JWT encode.
     *
     * @magentoConfigFixture current_store three_d_secure/cardinal/api_key K7cMG5u4be3dcMF10b7F1djCfmAf471f
     */
    public function testEncode()
    {
        $claims = [
            'jti' => 'a5a59bfb-ac06-4c5f-be5c-351b64ae608e',
            'iss' => '56560a358b946e0c8452365ds',
            'iat' => 1448997865,
            'OrgUnitId' => '565607c18b946e058463ds8r',
            'Payload' => [
                'OrderDetails' => [
                    'OrderNumber' => 125,
                    'Amount' => 1500,
                    'CurrencyCode' => 'USD'
                ]
            ],
            'ObjectifyPayload' => true
        ];

        $jwt = $this->model->encode($claims);

        $this->assertEquals(
            $this->getValidHS256Jwt(),
            $jwt,
            'Generated JWT isn\'t equal to expected'
        );
    }

    /**
     * Tests JWT decode.
     *
     * @magentoConfigFixture current_store three_d_secure/cardinal/api_key K7cMG5u4be3dcMF10b7F1djCfmAf471f
     */
    public function testDecode()
    {
        $tokenPayload = $this->model->decode($this->getValidJwt());

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
     * @magentoConfigFixture current_store three_d_secure/cardinal/api_key K7cMG5u4be3dcMF10b7F1djCfmAf471f
     */
    public function testDecodeWithException(string $jwt, string $errorMessage)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->model->decode($jwt);
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
                'errorMessage' => 'Unsupported input',
            ],
            [
                'jwt' => 'dddd.dddd.dddd',
                'errorMessage' => 'Unsupported input',
            ],
            [
                'jwt' => $this->getHS512Jwt(),
                'errorMessage' => 'The algorithm "HS512" is not supported.',
            ],
            [
                'jwt' => $this->getJwtWithInvalidSignature(),
                'errorMessage' => 'JWT signature verification failed',
            ],
        ];
    }

    /**
     * Gets JWT.
     *
     * @return string
     */
    private function getValidJwt(): string
    {
        return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJQYXlsb2FkIjp7IlZhbGlkYXRlZCI6dHJ1ZSwiUGF5bWVudCI6eyJUeXBlIj' .
            'oiQ0NBIiwiRXh0ZW5kZWREYXRhIjp7IkNBVlYiOiJBQUFCQVdGbG1RQUFBQUJqUldXWkVFRmdGeis9IiwiRUNJRmxhZyI6IjA1Iiwi' .
            'UEFSZXNTdGF0dXMiOiJZIiwiU2lnbmF0dXJlVmVyaWZpY2F0aW9uIjoiWSIsIlhJRCI6Ik1IRXlRakZSUWt0dGVtZHBhRmxSZEhvd1' .
            'dUQT0iLCJFbnJvbGxlZCI6IlkifX0sIkFjdGlvbkNvZGUiOiJTVUNDRVNTIiwiRXJyb3JOdW1iZXIiOjAsIkVycm9yRGVzY3JpcHRp' .
            'b24iOiJTdWNjZXNzIn0sImlhdCI6MTQ3MTAxNDQ5MiwiaXNzIjoiNTY1NjBhMzU4Yjk0NmUwYzg0NTIzNjVkcyIsImp0aSI6IjhhZj' .
            'M0ODExLWY5N2QtNDk1YS1hZDE5LWVjMmY2ODAwNGYyOCIsImV4cCI6MjUwOTcyNzE1MiwiQ3VzdG9tZXJTZXNzaW9uSWQiOiIwZTFh' .
            'ZTQ1MC1kZjJiLTQ4NzItOTRmNy1mMTI5YTJkZGFiMTgifQ.VSQxEzru-tUE62ACgBTnlwTuBEHSvCnfMOLPw7RYwVk';
    }

    /**
     * Returns valid JWT, signed using HS256.
     *
     * @return string
     */
    private function getValidHS256Jwt(): string
    {
        return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJPYmplY3RpZnlQYXlsb2FkIjp0cnVlLCJPcmdVbml0SWQiOiI1NjU2MDdjMThi' .
            'OTQ2ZTA1ODQ2M2RzOHIiLCJQYXlsb2FkIjp7Ik9yZGVyRGV0YWlscyI6eyJPcmRlck51bWJlciI6MTI1LCJBbW91bnQiOjE1MDAsIkN1' .
            'cnJlbmN5Q29kZSI6IlVTRCJ9fSwiaWF0IjoxNDQ4OTk3ODY1LCJpc3MiOiI1NjU2MGEzNThiOTQ2ZTBjODQ1MjM2NWRzIiwianRpIjoi' .
            'YTVhNTliZmItYWMwNi00YzVmLWJlNWMtMzUxYjY0YWU2MDhlIn0.NlzYRYEJ7rbhHalwKxHhckA3gDIvApcptQQMvSo3g1Y';
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
     * Gets token payload.
     *
     * @return array
     */
    private function getTokenPayload(): array
    {
        return [
            'Payload' => [
                'Validated' => true,
                'Payment' => [
                    'Type' => 'CCA',
                    'ExtendedData' => [
                        'CAVV' => 'AAABAWFlmQAAAABjRWWZEEFgFz+=',
                        'ECIFlag' => '05',
                        'PAResStatus' => 'Y',
                        'SignatureVerification' => 'Y',
                        'XID' => 'MHEyQjFRQkttemdpaFlRdHowWTA=',
                        'Enrolled' => 'Y'
                    ],
                ],
                'ActionCode' => 'SUCCESS',
                'ErrorNumber' => 0,
                'ErrorDescription' => 'Success',
            ],
            'iat' => 1471014492,
            'iss' => '56560a358b946e0c8452365ds',
            'jti' => '8af34811-f97d-495a-ad19-ec2f68004f28',
            'exp' => 2509727152, // 07/12/2049 18:25:52 GMT
            'CustomerSessionId' => '0e1ae450-df2b-4872-94f7-f129a2ddab18',
        ];
    }
}
