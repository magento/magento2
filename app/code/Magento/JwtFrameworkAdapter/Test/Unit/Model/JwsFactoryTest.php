<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Test\Unit\Model;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Payload\ArbitraryPayload;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Framework\Jwt\Payload\NestedPayloadInterface;
use Magento\JwtFrameworkAdapter\Model\JwsFactory;
use PHPUnit\Framework\TestCase;

class JwsFactoryTest extends TestCase
{
    /**
     * @var JwsFactory
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new JwsFactory();
    }

    public static function getCreateCases(): array
    {
        return [
            'compact-arbitrary' => [
                ['cty' => 'MyType', 'typ' => 'JWT'],
                'some-value',
                null,
                ArbitraryPayload::class
            ],
            'compact-claims' => [
                ['typ' => 'JWT'],
                '{"tst1":"val1","tst2":2,"tst3":true}',
                null,
                ClaimsPayloadInterface::class
            ],
            'compact-nested' => [
                ['typ' => 'JWT', 'cty' => NestedPayloadInterface::CONTENT_TYPE],
                'eyJhbGciOiJub25lIn0.'
                .'eyJpc3MiOiJqb2UiLA0KICJleHAiOjEzMDA4MTkzODAsDQogImh0dHA6Ly9leGFtcGxlLmNvbS9pc19yb290Ijp0cnVlfQ.',
                null,
                NestedPayloadInterface::class
            ],
            'json-arbitrary' => [
                ['typ' => 'JWT'],
                'arbitrary',
                ['cty' => 'SomeType'],
                ArbitraryPayload::class
            ],
            'json-claims' => [
                ['typ' => 'JWT'],
                '{"tst1":"val1","tst2":2,"tst3":true}',
                ['aud' => 'magento'],
                ClaimsPayloadInterface::class
            ]
        ];
    }

    /**
     * Test "create" method.
     *
     * @param array $headers
     * @param string $content
     * @param array|null $unprotected
     * @param string $payloadClass
     * @return void
     * @dataProvider getCreateCases
     */
    public function testCreate(
        array $headers,
        string $content,
        ?array $unprotected,
        string $payloadClass
    ): void {
        $jws = $this->model->create($headers, $content, $unprotected);

        $payload = $jws->getPayload();
        $this->assertEquals($content, $payload->getContent());
        $this->assertInstanceOf($payloadClass, $payload);
        if ($payload instanceof ClaimsPayloadInterface) {
            $actualClaims = [];
            foreach ($payload->getClaims() as $claim) {
                $actualClaims[$claim->getName()] = $claim->getValue();
            }
            $this->assertEquals(json_decode($content, true), $actualClaims);
        }

        $this->validateHeader($headers, $jws->getHeader());
        if ($unprotected === null) {
            $this->assertEmpty($jws->getUnprotectedHeaders());
        } else {
            $this->assertNotEmpty($jws->getUnprotectedHeaders());
            $this->validateHeader($unprotected, array_values($jws->getUnprotectedHeaders())[0]);
        }
    }

    private function validateHeader(array $expectedHeaders, HeaderInterface $actual): void
    {
        foreach ($expectedHeaders as $header => $value) {
            $parameter = $actual->getParameter($header);
            $this->assertNotNull($parameter);
            $this->assertEquals($value, $parameter->getValue());
        }
    }
}
