<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use DateTime;
use DateTimeZone;
use Generator;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPriceProcessor;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend_Db_Statement_Interface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReindexRuleProductsPriceProcessorTest extends TestCase
{
    /**
     * @var ReindexRuleProductsPriceProcessor
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RuleProductPricesPersistor|MockObject
     */
    private $pricesPersitorMock;

    /**
     * @var ProductPriceCalculator|MockObject
     */
    private $productPriceCalculatorMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->productPriceCalculatorMock = $this->createMock(ProductPriceCalculator::class);
        $this->pricesPersitorMock = $this->createMock(RuleProductPricesPersistor::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);

        $this->model = new ReindexRuleProductsPriceProcessor(
            $this->storeManagerMock,
            $this->productPriceCalculatorMock,
            $this->pricesPersitorMock,
            $this->localeDateMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $defaultGroupId = 11;
        $defaultStoreId = 22;

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->willReturn($defaultGroupId);
        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn($defaultStoreId);
        $this->storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->with($defaultGroupId)
            ->willReturn($groupMock);
        $this->productPriceCalculatorMock->expects($this->atLeastOnce())
            ->method('calculate');
        $this->pricesPersitorMock->expects($this->atLeastOnce())
            ->method('execute');
        $this->localeDateMock->expects($this->once())
            ->method('scopeDate')
            ->with($defaultStoreId, null, true)
            ->willReturn(new DateTime());

        $statementMock = $this->createMock(Zend_Db_Statement_Interface::class);

        $ruleData = [
            [
                'product_id' => 55,
                'website_id' => 234,
                'customer_group_id' => 2,
                'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
                'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
                'action_stop' => true
            ],
            [
                'product_id' => 66,
                'website_id' => 234,
                'customer_group_id' => 2,
                'from_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') - 100),
                'to_time' => mktime(0, 0, 0, (int)date('m'), (int)date('d') + 100),
                'action_stop' => true
            ]
        ];

        $statementMock
            ->method('fetch')
            ->willReturnOnConsecutiveCalls($ruleData[0], $ruleData[1], false);

        $this->model->execute(
            $statementMock,
            $websiteMock,
            100,
            true,
            true
        );
    }

    #[DataProvider('timezoneProvider')]
    public function testBatchesShareDatesPreserveStopRulesAndCloseBeforeAdvancing(bool $websiteTimezone): void
    {
        $website = $this->configureBatchWebsite($websiteTimezone);
        $first = $this->createMock(Zend_Db_Statement_Interface::class);
        $second = $this->createMock(Zend_Db_Statement_Interface::class);
        $first->method('fetch')->willReturnOnConsecutiveCalls(
            $this->ruleRow(10, true),
            $this->ruleRow(10, false),
            false
        );
        $second->method('fetch')->willReturnOnConsecutiveCalls($this->ruleRow(20, true), false);
        $closed = false;
        $first->expects($this->once())->method('closeCursor')->willReturnCallback(
            static function () use (&$closed): bool {
                $closed = true;
                return true;
            }
        );
        $second->expects($this->once())->method('closeCursor');
        $statements = (static function () use ($first, $second, &$closed): Generator {
            yield $first;
            self::assertTrue($closed, 'Release the first buffer before executing the next SELECT.');
            yield $second;
        })();
        $this->productPriceCalculatorMock->expects($this->exactly(6))->method('calculate')->willReturn(42.0);
        $outputs = [];
        $this->pricesPersitorMock->expects($this->exactly(2))->method('execute')
            ->willReturnCallback(static function (array $prices, bool $working) use (&$outputs): bool {
                self::assertTrue($working);
                $outputs[] = array_values($prices);
                return true;
            });

        $this->model->executeBatches($statements, $website, 5000, true, $websiteTimezone);

        self::assertCount(2, $outputs);
        foreach ($outputs as $index => $prices) {
            self::assertCount(3, $prices);
            self::assertSame(
                ['2026-10-22 23:59:59', '2026-10-23 12:00:00', '2026-10-24 00:00:00'],
                array_map(static fn (array $row): string => $row['rule_date']->format('Y-m-d H:i:s'), $prices)
            );
            self::assertSame(array_fill(0, 3, ($index + 1) * 10), array_column($prices, 'product_id'));
            self::assertSame([42.0, 42.0, 42.0], array_column($prices, 'rule_price'));
        }
    }

    public static function timezoneProvider(): array
    {
        return ['website timezone' => [true], 'default timezone' => [false]];
    }

    #[DataProvider('failureProvider')]
    public function testFailureClosesCursorAndDoesNotAdvance(string $failure): void
    {
        $website = $this->configureBatchWebsite(true);
        $statement = $this->createMock(Zend_Db_Statement_Interface::class);
        $exception = new RuntimeException('controlled ' . $failure . ' failure');
        if ($failure === 'fetch') {
            $statement->method('fetch')->willThrowException($exception);
            $this->pricesPersitorMock->expects($this->never())->method('execute');
        } else {
            $statement->method('fetch')->willReturnOnConsecutiveCalls($this->ruleRow(10, true), false);
            $this->productPriceCalculatorMock->method('calculate')->willReturn(42.0);
            $this->pricesPersitorMock->method('execute')->willThrowException($exception);
        }
        $statement->expects($this->once())->method('closeCursor');
        $advanced = false;
        $statements = (static function () use ($statement, &$advanced): Generator {
            yield $statement;
            $advanced = true;
        })();

        try {
            $this->model->executeBatches($statements, $website, 5000, true, true);
            self::fail('The processing failure must propagate.');
        } catch (RuntimeException $actual) {
            self::assertSame($exception, $actual);
            self::assertFalse($advanced);
        }
    }

    public static function failureProvider(): array
    {
        return ['fetch failure' => ['fetch'], 'persistence failure' => ['persist']];
    }

    private function configureBatchWebsite(bool $websiteTimezone): WebsiteInterface
    {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getDefaultGroupId')->willReturn(11);
        $group = $this->createMock(GroupInterface::class);
        $group->method('getDefaultStoreId')->willReturn(22);
        $this->storeManagerMock->method('getGroup')->with(11)->willReturn($group);
        $this->localeDateMock->expects($this->once())->method('scopeDate')
            ->with($websiteTimezone ? 22 : 0, null, true)
            ->willReturn(new DateTime('2026-10-23 12:00:00', new DateTimeZone('UTC')));
        return $website;
    }

    private function ruleRow(int $productId, bool $stop): array
    {
        return [
            'product_id' => $productId,
            'website_id' => 7,
            'customer_group_id' => 2,
            'from_time' => 0,
            'to_time' => 0,
            'action_stop' => $stop
        ];
    }
}
