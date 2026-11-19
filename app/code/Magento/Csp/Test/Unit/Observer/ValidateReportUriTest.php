<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Observer;

use Magento\Csp\Model\Mode\ReportUriValidator;
use Magento\Csp\Observer\ValidateReportUri;
use Magento\Framework\App\Config\Value;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Url as UrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidateReportUriTest extends TestCase
{
    /**
     * @var ValidateReportUri
     */
    private ValidateReportUri $observer;

    protected function setUp(): void
    {
        $this->observer = new ValidateReportUri(new ReportUriValidator(new UrlValidator()));
    }

    #[DataProvider('validValueDataProvider')]
    public function testAllowsValidValue(string $path, string $value): void
    {
        $this->observer->execute($this->createObserver($path, $value));
        $this->addToAssertionCount(1);
    }

    public static function validValueDataProvider(): array
    {
        return [
            'storefront https url' => ['csp/mode/storefront/report_uri', 'https://example.com/csp-report'],
            'admin http url' => ['csp/mode/admin/report_uri', 'http://example.com/csp-report'],
            'checkout root-relative path' => ['csp/mode/storefront_checkout_index_index/report_uri', '/csp-reports/'],
            'sales order create empty' => ['csp/mode/admin_sales_order_create_index/report_uri', ''],
        ];
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testRejectsInvalidValue(string $path, string $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid Report URI.');

        $this->observer->execute($this->createObserver($path, $value));
    }

    public static function invalidValueDataProvider(): array
    {
        return [
            'storefront header injection' => [
                'csp/mode/storefront/report_uri',
                "https://example.com/report\r\nX-Injected: 1",
            ],
            'admin not a url' => ['csp/mode/admin/report_uri', 'not-a-url'],
            'checkout javascript scheme' => [
                'csp/mode/storefront_checkout_index_index/report_uri',
                'javascript:alert(1)',
            ],
            'sales order create non-ascii byte' => [
                'csp/mode/admin_sales_order_create_index/report_uri',
                "/csp-reports/\xFF",
            ],
        ];
    }

    public function testIgnoresOtherPaths(): void
    {
        $this->observer->execute($this->createObserver('csp/mode/storefront/report_only', 'not-a-url'));
        $this->observer->execute($this->createObserver('web/unsecure/report_uri', 'not-a-url'));
        $this->addToAssertionCount(1);
    }

    public function testIgnoresNonConfigValueObject(): void
    {
        $event = new Event(['config_data' => new DataObject(['path' => 'csp/mode/admin/report_uri'])]);
        $this->observer->execute(new Observer(['event' => $event]));
        $this->addToAssertionCount(1);
    }

    public function testUsesFieldLabelInMessage(): void
    {
        $observer = $this->createObserver('csp/mode/admin/report_uri', 'not-a-url', ['label' => 'Admin Report URI']);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid Admin Report URI.');

        $this->observer->execute($observer);
    }

    private function createObserver(string $path, string $value, ?array $fieldConfig = null): Observer
    {
        $configValue = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $configValue->setPath($path);
        $configValue->setValue($value);
        if ($fieldConfig !== null) {
            $configValue->setData('field_config', $fieldConfig);
        }

        return new Observer(['event' => new Event(['config_data' => $configValue])]);
    }
}
