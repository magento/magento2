<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Policy\Renderer;

use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * Test that rendering policies via headers works.
 */
class SimplePolicyHeaderRendererTest extends TestCase
{
    /**
     * @var SimplePolicyHeaderRenderer
     */
    private $renderer;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->renderer = Bootstrap::getObjectManager()->get(SimplePolicyHeaderRenderer::class);
        $this->response = Bootstrap::getObjectManager()->create(HttpResponse::class);
    }

    /**
     * Test policy rendering in restrict mode.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 0
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri 0
     *
     * @return void
     */
    public function testRenderRestrictMode(): void
    {
        $policy = new FetchPolicy('default-src', false, ['https://magento.com'], [], true);

        $this->renderer->render($policy, $this->response);

        $this->assertNotEmpty($header = $this->response->getHeader('Content-Security-Policy'));
        $this->assertEmpty($this->response->getHeader('Content-Security-Policy-Report-Only'));
        $this->assertEquals('default-src https://magento.com \'self\';', $header->getFieldValue());
    }

    /**
     * Test policy rendering in restrict mode with report URL provided.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 0
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri /csp-reports/
     *
     * @return void
     */
    public function testRenderRestrictWithReportingMode(): void
    {
        $policy = new FetchPolicy('default-src', false, ['https://magento.com'], [], true);

        $this->renderer->render($policy, $this->response);

        $this->assertNotEmpty($header = $this->response->getHeader('Content-Security-Policy'));
        $this->assertEmpty($this->response->getHeader('Content-Security-Policy-Report-Only'));
        $this->assertEquals(
            'default-src https://magento.com \'self\'; report-uri /csp-reports/; report-to report-endpoint;',
            $header->getFieldValue()
        );
        $this->assertNotEmpty($reportToHeader = $this->response->getHeader('Report-To'));
        $this->assertNotEmpty($reportData = json_decode("[{$reportToHeader->getFieldValue()}]", true));
        $this->assertEquals('report-endpoint', $reportData[0]['group']);
    }

    /**
     * Test policy rendering in report-only mode.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 1
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri 0
     *
     * @return void
     */
    public function testRenderReportMode(): void
    {
        $policy = new FetchPolicy(
            'default-src',
            false,
            ['https://magento.com'],
            ['https'],
            true,
            true,
            true,
            ['5749837589457695'],
            ['B2yPHKaXnvFWtRChIbabYmUBFZdVfKKXHbWtWidDVF8=' => 'sha256'],
            true,
            true
        );

        $this->renderer->render($policy, $this->response);

        $this->assertNotEmpty($header = $this->response->getHeader('Content-Security-Policy-Report-Only'));
        $this->assertEmpty($this->response->getHeader('Content-Security-Policy'));
        $this->assertEquals(
            'default-src https://magento.com https: \'self\' \'unsafe-inline\' \'unsafe-eval\' \'strict-dynamic\''
            . ' \'unsafe-hashes\' \'nonce-'.base64_encode($policy->getNonceValues()[0]).'\''
            . ' \'sha256-B2yPHKaXnvFWtRChIbabYmUBFZdVfKKXHbWtWidDVF8=\';',
            $header->getFieldValue()
        );
    }

    /**
     * Test policy rendering in report-only mode with report URL provided.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 1
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri /csp-reports/
     *
     * @return void
     */
    public function testRenderReportWithReportingMode(): void
    {
        $policy = new FetchPolicy('default-src', false, ['https://magento.com'], [], true);

        $this->renderer->render($policy, $this->response);

        $this->assertNotEmpty($header = $this->response->getHeader('Content-Security-Policy-Report-Only'));
        $this->assertEmpty($this->response->getHeader('Content-Security-Policy'));
        $this->assertEquals(
            'default-src https://magento.com \'self\'; report-uri /csp-reports/; report-to report-endpoint;',
            $header->getFieldValue()
        );
        $this->assertNotEmpty($reportToHeader = $this->response->getHeader('Report-To'));
        $this->assertNotEmpty($reportData = json_decode("[{$reportToHeader->getFieldValue()}]", true));
        $this->assertEquals('report-endpoint', $reportData[0]['group']);
    }
}
