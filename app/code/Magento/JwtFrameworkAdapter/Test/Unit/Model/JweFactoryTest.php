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
use Magento\JwtFrameworkAdapter\Model\JweFactory;
use PHPUnit\Framework\TestCase;

class JweFactoryTest extends TestCase
{
    /**
     * @var JweFactory
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new JweFactory();
    }

    public function getCreateCases(): array
    {
        return [
            'compact-arbitrary' => [
                ['cty' => 'MyType', 'typ' => 'JWT'],
                'some-value',
                null,
                null,
                ArbitraryPayload::class
            ],
            'compact-claims' => [
                ['typ' => 'JWT'],
                '{"tst1":"val1","tst2":2,"tst3":true}',
                null,
                null,
                ClaimsPayloadInterface::class
            ],
            'compact-nested' => [
                ['typ' => 'JWT', 'cty' => NestedPayloadInterface::CONTENT_TYPE],
                'eyJhbGciOiJub25lIn0.'
                .'eyJpc3MiOiJqb2UiLA0KICJleHAiOjEzMDA4MTkzODAsDQogImh0dHA6Ly9leGFtcGxlLmNvbS9pc19yb290Ijp0cnVlfQ.',
                null,
                null,
                NestedPayloadInterface::class
            ],
            'json-arbitrary' => [
                ['typ' => 'JWT'],
                'arbitrary',
                ['cty' => 'SomeType'],
                ['crit' => 'exp'],
                ArbitraryPayload::class
            ],
            'json-claims' => [
                ['typ' => 'JWT'],
                '{"tst1":"val1","tst2":2,"tst3":true}',
                ['aud' => 'magento'],
                ['custom' => 'value'],
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
     * @param array|null $perRecipient
     * @param string $payloadClass
     * @return void
     * @dataProvider getCreateCases
     */
    public function testCreate(
        array $headers,
        string $content,
        ?array $unprotected,
        ?array $perRecipient,
        string $payloadClass
    ): void {
        $jwe = $this->model->create($headers, $content, $unprotected, $perRecipient);

        $payload = $jwe->getPayload();
        $this->assertEquals($content, $payload->getContent());
        $this->assertInstanceOf($payloadClass, $payload);
        if ($payload instanceof ClaimsPayloadInterface) {
            $actualClaims = [];
            foreach ($payload->getClaims() as $claim) {
                $actualClaims[$claim->getName()] = $claim->getValue();
            }
            $this->assertEquals(json_decode($content, true), $actualClaims);
        }

        $this->validateHeader($headers, $jwe->getHeader());
        if ($unprotected === null) {
            $this->assertNull($jwe->getSharedUnprotectedHeader());
        } else {
            $this->assertNotNull($jwe->getSharedUnprotectedHeader());
            $this->validateHeader($unprotected, $jwe->getSharedUnprotectedHeader());
        }
        if ($perRecipient === null) {
            $this->assertNull($jwe->getSharedUnprotectedHeader());
        } else {
            $this->assertNotNull($jwe->getSharedUnprotectedHeader());
            $this->validateHeader($unprotected, $jwe->getSharedUnprotectedHeader());
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
