<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Config\Backend;

use Magento\Csp\Model\Config\Backend\ReportUri;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\Url as UrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReportUriTest extends TestCase
{
    /**
     * @var ReportUri
     */
    private ReportUri $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            ReportUri::class,
            [
                'context' => $objectManager->getObject(Context::class),
                'registry' => $this->createMock(Registry::class),
                'config' => $this->createMock(ScopeConfigInterface::class),
                'cacheTypeList' => $this->createMock(TypeListInterface::class),
                'urlValidator' => new UrlValidator(),
            ]
        );
    }

    #[DataProvider('validValueDataProvider')]
    public function testBeforeSaveAllowsValidValue(string $value): void
    {
        $this->model->setValue($value);
        $this->model->beforeSave();

        $this->assertSame($value, $this->model->getValue());
    }

    public static function validValueDataProvider(): array
    {
        return [
            'https url' => ['https://example.com/csp-report'],
            'http url' => ['http://example.com/csp-report'],
        ];
    }

    public function testBeforeSaveAllowsEmptyValue(): void
    {
        $this->model->setValue('');
        $this->model->beforeSave();

        $this->assertSame('', $this->model->getValue());
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testBeforeSaveThrowsOnInvalidValue(string $value): void
    {
        $this->expectException(LocalizedException::class);

        $this->model->setValue($value);
        $this->model->beforeSave();
    }

    public static function invalidValueDataProvider(): array
    {
        return [
            'carriage return and line feed' => ["https://example.com/report\r\nX-Injected: 1"],
            'not a url' => ['not-a-url'],
            'javascript scheme' => ['javascript:alert(1)'],
            'ftp scheme' => ['ftp://example.com/report'],
        ];
    }
}
