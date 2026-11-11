<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Policy\Renderer;

use Magento\Csp\Api\ModeConfigManagerInterface;
use Magento\Csp\Model\Mode\Data\ModeConfigured;
use Magento\Csp\Model\Policy\FlagPolicy;
use Magento\Csp\Model\Policy\Renderer\SimplePolicyHeaderRenderer;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SimplePolicyHeaderRendererTest extends TestCase
{
    #[DataProvider('policyModeProvider')]
    public function testConfiguredEndpointIsAvailableToModernAndLegacyBrowsers(bool $reportOnly, string $header): void
    {
        $uri = 'https://reports.example/csp?stores=one%2Ctwo';
        $renderer = $this->createRenderer($reportOnly, $uri);
        $response = new Response();

        $renderer->render(new FlagPolicy('upgrade-insecure-requests'), $response);
        $renderer->render(new FlagPolicy('block-all-mixed-content'), $response);

        $modernHeader = $response->getHeader('Reporting-Endpoints');
        self::assertNotFalse($modernHeader);
        self::assertSame('report-endpoint="' . $uri . '"', $modernHeader->getFieldValue());
        $legacyHeader = json_decode($response->getHeader('Report-To')->getFieldValue(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('report-endpoint', $legacyHeader['group']);
        self::assertSame([['url' => $uri]], $legacyHeader['endpoints']);
        $policy = $response->getHeader($header)->getFieldValue();
        self::assertSame(1, substr_count($policy, 'report-to report-endpoint;'));
        self::assertSame(1, substr_count($policy, 'report-uri ' . $uri . ';'));
    }

    public static function policyModeProvider(): array
    {
        return [
            'enforce' => [false, 'Content-Security-Policy'],
            'report only' => [true, 'Content-Security-Policy-Report-Only'],
        ];
    }

    public function testNoReportingHeadersWithoutConfiguredEndpoint(): void
    {
        $response = new Response();
        $this->createRenderer(true, null)->render(new FlagPolicy('upgrade-insecure-requests'), $response);

        self::assertFalse($response->getHeader('Reporting-Endpoints'));
        self::assertFalse($response->getHeader('Report-To'));
        $policy = $response->getHeader('Content-Security-Policy-Report-Only')->getFieldValue();
        self::assertStringNotContainsString('report-to', $policy);
    }

    public function testExistingReportingEndpointsArePreserved(): void
    {
        $response = new Response();
        $existing = 'default="https://reports.example/browser"';
        $response->setHeader('Reporting-Endpoints', $existing);
        $uri = 'https://reports.example/csp';

        $this->createRenderer(true, $uri)->render(new FlagPolicy('upgrade-insecure-requests'), $response);

        self::assertSame(
            'report-endpoint="' . $uri . '", ' . $existing,
            $response->getHeader('Reporting-Endpoints')->getFieldValue()
        );
    }

    public function testExistingLegacyReportingConfigurationIsPreserved(): void
    {
        $response = new Response();
        $existing = '{"group":"custom","endpoints":[{"url":"https://reports.example/custom"}]}';
        $response->setHeader('Report-To', $existing);

        $this->createRenderer(false, 'https://reports.example/csp')
            ->render(new FlagPolicy('upgrade-insecure-requests'), $response);

        self::assertSame($existing, $response->getHeader('Report-To')->getFieldValue());
        self::assertFalse($response->getHeader('Reporting-Endpoints'));
    }

    private function createRenderer(bool $reportOnly, ?string $uri): SimplePolicyHeaderRenderer
    {
        $modeConfig = $this->createStub(ModeConfigManagerInterface::class);
        $modeConfig->method('getConfigured')->willReturn(new ModeConfigured($reportOnly, $uri));

        return new SimplePolicyHeaderRenderer($modeConfig);
    }
}
