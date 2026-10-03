<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var StorePaymentMethod
     */
    private $model;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($this->connection);
        $resource->method('getTableName')->willReturnArgument(0);
        $this->model = new StorePaymentMethod(
            $this->createMock(ContextInterface::class),
            $this->createMock(UiComponentFactory::class),
            $this->scopeConfig,
            $resource,
            [],
            ['name' => 'payment_method']
        );
    }

    public function testPrepareDataSourceResolvesTitlePerRowStore(): void
    {
        $titles = [1 => 'Check One', 2 => 'Cheque Two'];
        $this->connection->expects($this->once())->method('fetchPairs')->willReturn([10 => '1', 11 => '2']);
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
                    ['entity_id' => '10', 'store_id' => 'Main Website', 'payment_method' => 'checkmo'],
                    ['entity_id' => '11', 'store_id' => 'Other Website', 'payment_method' => 'checkmo'],
                ],
            ],
        ]);

        $this->assertSame('Check One', $result['data']['items'][0]['payment_method']);
        $this->assertSame('Cheque Two', $result['data']['items'][1]['payment_method']);
    }

    public function testPrepareDataSourceKeepsCodeWhenTitleIsNotConfigured(): void
    {
        $this->connection->method('fetchPairs')->willReturn([10 => '1']);
        $this->scopeConfig->method('getValue')->willReturn(null);

        $result = $this->model->prepareDataSource([
            'data' => ['items' => [['entity_id' => '10', 'payment_method' => 'removed_method']]],
        ]);

        $this->assertSame('removed_method', $result['data']['items'][0]['payment_method']);
    }

    public function testPrepareDataSourceFallsBackToDefaultScopeWhenStoreIsUnknown(): void
    {
        $this->connection->method('fetchPairs')->willReturn([10 => '99']);
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(
                function (...$args) {
                    if (($args[2] ?? null) === 99) {
                        throw new NoSuchEntityException(__('Store does not exist'));
                    }
                    return 'Default Title';
                }
            );

        $result = $this->model->prepareDataSource([
            'data' => ['items' => [['entity_id' => '10', 'store_id' => 'x', 'payment_method' => 'checkmo']]],
        ]);

        $this->assertSame('Default Title', $result['data']['items'][0]['payment_method']);
    }

    public function testPrepareDataSourceSkipsLookupWithoutPaymentCodes(): void
    {
        $this->connection->expects($this->never())->method('fetchPairs');

        $result = $this->model->prepareDataSource([
            'data' => ['items' => [['entity_id' => '10', 'payment_method' => '']]],
        ]);

        $this->assertSame('', $result['data']['items'][0]['payment_method']);
    }
}
