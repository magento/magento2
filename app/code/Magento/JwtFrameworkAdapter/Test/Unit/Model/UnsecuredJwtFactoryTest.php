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
use Magento\JwtFrameworkAdapter\Model\UnsecuredJwtFactory;
use PHPUnit\Framework\TestCase;

class UnsecuredJwtFactoryTest extends TestCase
{
    /**
     * @var UnsecuredJwtFactory
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new UnsecuredJwtFactory();
    }

    public function getCreateCases(): array
    {
        return [
            'compact-arbitrary' => [
                [['cty' => 'MyType', 'typ' => 'JWT']],
                'some-value',
                null,
                ArbitraryPayload::class
            ],
            'compact-claims' => [
                [['typ' => 'JWT']],
                '{"tst1":"val1","tst2":2,"tst3":true}',
                null,
                ClaimsPayloadInterface::class
            ],
            'compact-nested' => [
                [['typ' => 'JWT', 'cty' => NestedPayloadInterface::CONTENT_TYPE]],
                'eyJhbGciOiJub25lIn0.'
                .'eyJpc3MiOiJqb2UiLA0KICJleHAiOjEzMDA4MTkzODAsDQogImh0dHA6Ly9leGFtcGxlLmNvbS9pc19yb290Ijp0cnVlfQ.',
                null,
                NestedPayloadInterface::class
            ],
            'json-flat-arbitrary' => [
                [['typ' => 'JWT']],
                'arbitrary',
                [['cty' => 'SomeType']],
                ArbitraryPayload::class
            ],
            'json-flat-claims' => [
                [['typ' => 'JWT']],
                '{"tst1":"val1","tst2":2,"tst3":true}',
                [['aud' => 'magento']],
                ClaimsPayloadInterface::class
            ],
            'json-arbitrary' => [
                [['typ' => 'JWT'], ['typ' => 'JWT', 'aud' => 'magento']],
                'value',
                [['cty' => 'MyType'], ['cty' => 'MyType', 'crit' => 'exp']],
                ArbitraryPayload::class
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
        $jwt = $this->model->create($headers, $unprotected, $content);

        $payload = $jwt->getPayload();
        $this->assertEquals($content, $payload->getContent());
        $this->assertInstanceOf($payloadClass, $payload);
        if ($payload instanceof ClaimsPayloadInterface) {
            $actualClaims = [];
            foreach ($payload->getClaims() as $claim) {
                $actualClaims[$claim->getName()] = $claim->getValue();
            }
            $this->assertEquals(json_decode($content, true), $actualClaims);
        }

        $actualHeaders = array_map([$this, 'extractHeader'], $jwt->getProtectedHeaders());
        $this->assertEquals($headers, $actualHeaders);
    }

    private function extractHeader(HeaderInterface $header): array
    {
        $values = [];
        foreach ($header->getParameters() as $parameter) {
            $values[$parameter->getName()] = $parameter->getValue();
        }

        return $values;
    }
}
