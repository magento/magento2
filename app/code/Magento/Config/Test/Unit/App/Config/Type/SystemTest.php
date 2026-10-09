<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\App\Config\Type;

use Magento\Config\App\Config\Type\System;
use Magento\Config\App\Config\Type\System\Reader;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Config\Placeholder;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Store\Model\Config\Processor\Placeholder as PlaceholderProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SystemTest extends TestCase
{
    private const HOST_A = 'https://base.test/';
    private const HOST_B = 'https://base2.test/';

    /**
     * @var string[]
     */
    private array $cacheStorage = [];

    public function testBaseUrlPlaceholderIsResolvedForTheCurrentRequestAfterCacheWarmup(): void
    {
        $systemOnHostA = $this->createSystem(self::HOST_A);
        $this->assertSame(self::HOST_A, $systemOnHostA->get('websites/base/web/unsecure/base_link_url'));
        $this->assertNotEmpty($this->cacheStorage);

        $systemOnHostB = $this->createSystem(self::HOST_B);
        $this->assertSame(self::HOST_B, $systemOnHostB->get('websites/base2/web/unsecure/base_url'));
        $this->assertSame(self::HOST_B, $systemOnHostB->get('websites/base2/web/unsecure/base_link_url'));
        $this->assertSame(self::HOST_B, $systemOnHostB->get('websites/base2/web/secure/base_link_url'));
        $this->assertSame(self::HOST_B . 'media/', $systemOnHostB->get('websites/base2/web/unsecure/base_media_url'));
        $this->assertSame(self::HOST_B, $systemOnHostB->get('default/web/unsecure/base_url'));
        $this->assertSame(
            ['base_url' => self::HOST_B, 'base_link_url' => self::HOST_B],
            $systemOnHostB->get('websites/base2/web/secure')
        );
    }

    public function testRequestHostIsNotWrittenToConfigCache(): void
    {
        $this->createSystem(self::HOST_A)->get('websites/base/web/unsecure/base_url');

        $this->assertNotEmpty($this->cacheStorage);
        foreach ($this->cacheStorage as $cacheId => $cachedData) {
            $this->assertStringNotContainsString('base.test', $cachedData, $cacheId);
        }
    }

    private function createSystem(string $distroBaseUrl): System
    {
        $request = $this->createStub(Http::class);
        $request->method('getDistroBaseUrl')->willReturn($distroBaseUrl);
        $urlPaths = ['unsecureBaseUrl' => 'web/unsecure/base_url', 'secureBaseUrl' => 'web/secure/base_url'];

        $reader = $this->createStub(Reader::class);
        $reader->method('read')->willReturn($this->getRawConfig());

        $cache = $this->createStub(FrontendInterface::class);
        $cache->method('load')->willReturnCallback(fn (string $id) => $this->cacheStorage[$id] ?? false);
        $cache->method('save')->willReturnCallback(
            function (string $data, string $id): bool {
                $this->cacheStorage[$id] = $data;
                return true;
            }
        );

        $encryptor = $this->createStub(Encryptor::class);
        $encryptor->method('encryptWithFastestAvailableAlgorithm')->willReturnArgument(0);
        $encryptor->method('decrypt')->willReturnArgument(0);

        $lockQuery = $this->createStub(LockGuardedCacheLoader::class);
        $lockQuery->method('lockedLoadData')->willReturnCallback(
            function (...$arguments) {
                [, $load, $collect, $save] = $arguments;
                $data = $load();
                if ($data === false) {
                    $data = $collect();
                    $save($data);
                }
                return $data;
            }
        );

        $cacheState = $this->createStub(StateInterface::class);
        $cacheState->method('isEnabled')->willReturn(true);

        return new System(
            source: $this->createStub(ConfigSourceInterface::class),
            postProcessor: new PlaceholderProcessor(new Placeholder($request, $urlPaths, '{{base_url}}', false)),
            fallback: $this->createStub(Fallback::class),
            cache: $cache,
            serializer: new Json(),
            preProcessor: $this->createStub(PreProcessorInterface::class),
            reader: $reader,
            encryptor: $encryptor,
            lockQuery: $lockQuery,
            cacheState: $cacheState,
            logger: $this->createStub(LoggerInterface::class),
            placeholder: new Placeholder($request, $urlPaths, '{{base_url}}')
        );
    }

    private function getRawConfig(): array
    {
        $scopeConfig = [
            'web' => [
                'unsecure' => [
                    'base_url' => '{{base_url}}',
                    'base_link_url' => '{{unsecure_base_url}}',
                    'base_media_url' => '{{unsecure_base_url}}media/',
                ],
                'secure' => [
                    'base_url' => '{{base_url}}',
                    'base_link_url' => '{{secure_base_url}}',
                ],
            ],
        ];

        return [
            'default' => $scopeConfig,
            'websites' => [
                'base' => $scopeConfig,
                'base2' => $scopeConfig,
            ],
        ];
    }
}
