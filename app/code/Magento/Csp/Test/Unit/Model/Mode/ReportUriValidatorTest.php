<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Mode;

use Magento\Csp\Model\Mode\ReportUriValidator;
use Magento\Framework\Validator\Url as UrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReportUriValidatorTest extends TestCase
{
    /**
     * @var ReportUriValidator
     */
    private ReportUriValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ReportUriValidator(new UrlValidator());
    }

    #[DataProvider('validReportUriDataProvider')]
    public function testValidReportUri(string $reportUri): void
    {
        $this->assertTrue($this->validator->isValid($reportUri));
    }

    public static function validReportUriDataProvider(): array
    {
        return [
            'https url' => ['https://example.com/csp-report'],
            'http url' => ['http://example.com/csp-report'],
            'root-relative path' => ['/csp-reports/'],
            'root-relative path with query' => ['/cspEndpoint/?source=storefront'],
        ];
    }

    #[DataProvider('invalidReportUriDataProvider')]
    public function testInvalidReportUri(string $reportUri): void
    {
        $this->assertFalse($this->validator->isValid($reportUri));
    }

    public static function invalidReportUriDataProvider(): array
    {
        return [
            'empty' => [''],
            'carriage return and line feed' => ["https://example.com/report\r\nX-Injected: 1"],
            'line feed in path' => ["/csp-reports/\nX-Injected: 1"],
            'space' => ['https://example.com/report other'],
            'directive separator' => ['/csp-reports/; script-src *'],
            'policy separator' => ['https://example.com/report,https://evil.example'],
            'not a url' => ['not-a-url'],
            'javascript scheme' => ['javascript:alert(1)'],
            'ftp scheme' => ['ftp://example.com/report'],
            'non-ascii byte' => ["/csp-reports/\xFF"],
        ];
    }
}
