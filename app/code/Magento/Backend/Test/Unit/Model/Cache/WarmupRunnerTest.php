<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Cache;

use Magento\Backend\Model\Cache\WarmupRunner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class WarmupRunnerTest extends TestCase
{
    public function testRunOutputsProgressAndFinished(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->expects($this->once())->method('setOptions');
        $curl->expects($this->once())->method('get')->with('http://example.test/');
        $curl->method('getStatus')->willReturn(200);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            static function (string $path, ?string $scope = null, ?string $scopeCode = null) {
                if ($path === 'dev/cache_warmup/urls') {
                    return 'http://example.test/';
                }
                if ($path === 'dev/cache_warmup/timeout') {
                    return 30;
                }
                return null;
            }
        );

        $storeManager = $this->createMock(StoreManagerInterface::class);

        $runner = new WarmupRunner($curl, $scopeConfig, $storeManager);
        $output = new BufferedOutput();
        $ok = $runner->run($output);
        $text = $output->fetch();

        $this->assertSame(1, $ok);
        $this->assertStringContainsString('Cache warmup: in progress', $text);
        $this->assertStringContainsString('Stage 1/1:', $text);
        $this->assertStringContainsString('Cache warmup finished', $text);
    }

    public function testRunNoUrlsWhenStoreUnavailable(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->expects($this->never())->method('get');

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            static function (string $path, ?string $scope = null, ?string $scopeCode = null) {
                if ($path === 'dev/cache_warmup/urls' && $scope === ScopeInterface::SCOPE_STORE) {
                    return '';
                }
                return null;
            }
        );

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willThrowException(new \RuntimeException('no store'));

        $runner = new WarmupRunner($curl, $scopeConfig, $storeManager);
        $output = new BufferedOutput();
        $ok = $runner->run($output);

        $this->assertSame(0, $ok);
        $this->assertStringContainsString('No warmup URLs configured', $output->fetch());
    }
}
