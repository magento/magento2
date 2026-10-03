<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Ui\Component\Listing\Column\StorePaymentMethod;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorePaymentMethodTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var StorePaymentMethod
     */
    private $model;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->model = new StorePaymentMethod(
            $this->createMock(ContextInterface::class),
            $this->createMock(UiComponentFactory::class),
            $this->scopeConfig,
            [],
            ['name' => 'payment_method']
        );
    }

    public function testPrepareDataSourceResolvesTitlePerRowStore(): void
    {
        $titles = [1 => 'Check One', 2 => 'Cheque Two'];
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(
                fn (string $path, string $scope, $storeId) => $path === 'payment/checkmo/title'
                    && $scope === ScopeInterface::SCOPE_STORE
                    ? $titles[$storeId] ?? 'Default Title'
                    : ''
            );

        $result = $this->model->prepareDataSource([
            'data' => [
                'items' => [
                    ['store_id' => '1', 'payment_method' => 'checkmo'],
                    ['store_id' => '2', 'payment_method' => 'checkmo'],
                ],
            ],
        ]);

        $this->assertSame('Check One', $result['data']['items'][0]['payment_method']);
        $this->assertSame('Cheque Two', $result['data']['items'][1]['payment_method']);
    }

    public function testPrepareDataSourceKeepsCodeWhenTitleIsNotConfigured(): void
    {
        $this->scopeConfig->method('getValue')->willReturn(null);

        $result = $this->model->prepareDataSource([
            'data' => ['items' => [['store_id' => '1', 'payment_method' => 'removed_method']]],
        ]);

        $this->assertSame('removed_method', $result['data']['items'][0]['payment_method']);
    }

    public function testPrepareDataSourceFallsBackToDefaultScopeWithoutNumericStoreId(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/title', ScopeInterface::SCOPE_STORE, null)
            ->willReturn('Default Title');

        $result = $this->model->prepareDataSource([
            'data' => ['items' => [['store_id' => 'Main Website', 'payment_method' => 'checkmo']]],
        ]);

        $this->assertSame('Default Title', $result['data']['items'][0]['payment_method']);
    }
}
