<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Hash\Generator;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify deployment configuration hash storage and cache invalidation.
 */
class HashTest extends TestCase
{
    /**
     * @var Generator|MockObject
     */
    private $configHashGeneratorMock;

    /**
     * @var DataCollector|MockObject
     */
    private $dataConfigCollectorMock;

    /**
     * @var FlagFactory|MockObject
     */
    private $flagFactoryMock;

    /**
     * @var FlagResource|MockObject
     */
    private $flagResourceMock;

    /**
     * @var Flag|MockObject
     */
    private $flagMock;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->flagResourceMock = $this->getMockBuilder(FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHashGeneratorMock = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConfigCollectorMock = $this->getMockBuilder(DataCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache = $this->createMock(CacheInterface::class);
        $this->hash = new Hash(
            $this->configHashGeneratorMock,
            $this->dataConfigCollectorMock,
            $this->flagResourceMock,
            $this->flagFactoryMock,
            $this->cache
        );
    }

    /**
     * @param string|array|null $dataFromStorage
     * @param array $expectedResult
     * @return void
     */
    #[DataProvider('getDataProvider')]
    public function testGet($dataFromStorage, $expectedResult)
    {
        $this->cache->expects($this->never())->method('clean');
        $this->flagMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn($dataFromStorage);
        $this->flagFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => ['flag_code' => Hash::CONFIG_KEY]])
            ->willReturn($this->flagMock);
        $this->flagResourceMock->expects($this->once())
            ->method('load')
            ->with($this->flagMock, Hash::CONFIG_KEY, 'flag_code')
            ->willReturnSelf();

        $this->assertSame($expectedResult, $this->hash->get());
    }

    /**
     * @return array
     */
    public static function getDataProvider()
    {
        return [
            [['section' => 'hash'], ['section' => 'hash']],
            ['hash', ['hash']],
            ['', []],
            [null, []],
        ];
    }

    /**
     * @return void
     */
    public function testRegenerate()
    {
        $this->assertRegenerateInvalidatesCache(null);
    }

    /**
     * Verify section regeneration invalidates cached checks.
     *
     * @return void
     */
    public function testSectionRegenerateInvalidatesCache(): void
    {
        $this->assertRegenerateInvalidatesCache('section');
    }

    /**
     * Verify cache backend errors propagate after the hashes have been saved.
     *
     * @return void
     */
    public function testRegenerateWithCacheException(): void
    {
        $this->generalRegenerateMocks(['section' => 'config'], 'config', 'hash', ['section' => 'hash']);
        $this->flagResourceMock->expects($this->once())->method('save')->with($this->flagMock)->willReturnSelf();
        $exception = new \RuntimeException('Cache cleanup failed.');
        $this->cache->expects($this->once())
            ->method('clean')
            ->with(CacheConstants::CLEANING_MODE_MATCHING_TAG, [Hash::CACHE_TAG])
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);
        $this->hash->regenerate();
    }

    /**
     * Verify cached checks are invalidated only after the hash save succeeds.
     *
     * @param string|null $sectionName
     * @return void
     */
    private function assertRegenerateInvalidatesCache(?string $sectionName): void
    {
        $section = 'section';
        $config = 'some config';
        $fullConfig = ['section' => $config];
        $hash = 'some hash';
        $hashes = [$section => $hash];

        $this->generalRegenerateMocks($fullConfig, $config, $hash, $hashes, $sectionName);

        $saved = false;
        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock)
            ->willReturnCallback(function () use (&$saved) {
                $saved = true;
                return $this->flagResourceMock;
            });
        $this->cache->expects($this->once())
            ->method('clean')
            ->with(CacheConstants::CLEANING_MODE_MATCHING_TAG, [Hash::CACHE_TAG])
            ->willReturnCallback(function () use (&$saved) {
                $this->assertTrue($saved);
                return true;
            });

        $this->hash->regenerate($sectionName);
    }

    /**
     * @return void
     */
    public function testRegenerateWithException()
    {
        $this->cache->expects($this->never())->method('clean');
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The hash isn\'t saved.');
        $section = 'section';
        $config = 'some config';
        $fullConfig = ['section' => $config];
        $hash = 'some hash';
        $hashes = [$section => $hash];

        $this->generalRegenerateMocks($fullConfig, $config, $hash, $hashes, $section);

        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock)
            ->willThrowException(new \Exception('Some error'));
        $this->hash->regenerate($section);
    }

    /**
     * @param array $fullConfig
     * @param string $config
     * @param string $hash
     * @param array $hashes
     * @param string|null $sectionName
     * @return void
     */
    private function generalRegenerateMocks($fullConfig, $config, $hash, $hashes, $sectionName = null)
    {
        $this->dataConfigCollectorMock->expects($this->once())
            ->method('getConfig')
            ->with($sectionName)
            ->willReturn($fullConfig);
        $this->configHashGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($config)
            ->willReturn($hash);
        $this->flagMock->expects($this->once())
            ->method('setFlagData')
            ->willReturn($hashes);
        $this->flagFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => ['flag_code' => Hash::CONFIG_KEY]])
            ->willReturn($this->flagMock);
        $this->flagResourceMock->expects($this->exactly(2))
            ->method('load')
            ->with($this->flagMock, Hash::CONFIG_KEY, 'flag_code');
    }
}
