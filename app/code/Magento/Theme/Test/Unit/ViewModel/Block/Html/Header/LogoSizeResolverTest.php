<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\ViewModel\Block\Html\Header;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\ViewModel\Block\Html\Header\LogoSizeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test logo size resolver view model
 */
class LogoSizeResolverTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var LogoSizeResolver
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->model = new LogoSizeResolver($this->scopeConfig);
    }

    /**
     * @param string|null $configValue
     * @param int|null $expectedValue
     * @dataProvider configValueDataProvider
     */
    public function testGetWidth(?string $configValue, ?int $expectedValue): void
    {
        $storeId = 1;
        $this->scopeConfig->method('getValue')
            ->with('design/header/logo_width', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($configValue);
        $this->assertEquals($expectedValue, $this->model->getWidth($storeId));
    }

    /**
     * @param string|null $configValue
     * @param int|null $expectedValue
     * @dataProvider configValueDataProvider
     */
    public function testGetHeight(?string $configValue, ?int $expectedValue): void
    {
        $storeId = 1;
        $this->scopeConfig->method('getValue')
            ->with('design/header/logo_height', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($configValue);
        $this->assertEquals($expectedValue, $this->model->getHeight($storeId));
    }

    /**
     * @return array
     */
    public static function configValueDataProvider(): array
    {
        return [
            [null, null],
            ['', null],
            ['0', 0],
            ['180', 180],
        ];
    }
}
