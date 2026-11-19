<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheCleanCommand;
use Magento\Framework\App\Cache\Frontend\Pool as FrontendPool;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Tester\CommandTester;

class CacheCleanCommandTest extends AbstractCacheManageCommandTestCase
{
    /** @var FrontendPool */
    private $frontendPool;

    protected function setUp(): void
    {
        $this->cacheEventName = 'adminhtml_cache_flush_system';
        parent::setUp();
        $this->frontendPool = $this->createMock(FrontendPool::class);
        $this->command = new CacheCleanCommand(
            $this->cacheManagerMock,
            $this->eventManagerMock,
            $this->frontendPool
        );
    }

    /**
     * @param array $param
     * @param array $types
     * @param bool $shouldDispatch
     * @param string $output
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute($param, $types, $shouldDispatch, $output)
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn([
            'A', 'B', 'C', 'full_page'
        ]);
        $this->cacheManagerMock->expects($this->once())->method('clean')->with($types);

        if ($shouldDispatch) {
            $this->eventManagerMock->expects($this->once())->method('dispatch')->with($this->cacheEventName);
        } else {
            $this->eventManagerMock->expects($this->never())->method('dispatch');
        }

        if (empty($param['types'])) {
            $this->expectAllMagentoFrontendsAreCleaned();
        } else {
            $this->frontendPool->expects($this->never())->method('rewind');
        }

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public static function executeDataProvider(): array
    {
        return parent::executeDataProvider() + [
            'explicit all types' => [
                ['types' => ['A', 'B', 'C', 'full_page']],
                ['A', 'B', 'C', 'full_page'],
                true,
                static::getExpectedExecutionOutput(['A', 'B', 'C', 'full_page']),
            ],
        ];
    }

    /**
     * When no types are selected, all MAGE-scoped frontends must be cleaned,
     * without directly flushing their underlying storage.
     *
     * @return void
     */
    private function expectAllMagentoFrontendsAreCleaned(): void
    {
        $defaultFrontend = $this->createMock(FrontendInterface::class);
        $pageCacheFrontend = $this->createMock(FrontendInterface::class);
        $defaultFrontend->expects($this->once())->method('clean');
        $pageCacheFrontend->expects($this->once())->method('clean');
        $defaultFrontend->expects($this->never())->method('getBackend');
        $pageCacheFrontend->expects($this->never())->method('getBackend');

        $this->frontendPool->expects($this->once())->method('rewind');
        $this->frontendPool->expects($this->exactly(3))->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false);
        $this->frontendPool->expects($this->exactly(2))->method('current')
            ->willReturnOnConsecutiveCalls($defaultFrontend, $pageCacheFrontend);
        $this->frontendPool->expects($this->exactly(2))->method('next');
    }

    /**
     * Get expected output based on set of types operated on
     *
     * @param array $types
     * @return string
     */
    public static function getExpectedExecutionOutput(array $types)
    {
        return 'Cleaned cache types:' . PHP_EOL . implode(PHP_EOL, $types) . PHP_EOL;
    }
}
