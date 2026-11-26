<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\BackendWrapper;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\PruneableInterface;

/**
 * Unit test for BackendWrapper
 */
class BackendWrapperTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var TagAdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var FrontendInterface|MockObject
     */
    private $symfonyMock;

    /**
     * @var BackendWrapper
     */
    private BackendWrapper $backendWrapper;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheMock = $this->createMock(CacheItemPoolInterface::class);
        $this->adapterMock = $this->createMock(TagAdapterInterface::class);
        $this->symfonyMock = $this->createMock(FrontendInterface::class);

        $this->backendWrapper = new BackendWrapper(
            $this->cacheMock,
            $this->adapterMock,
            $this->symfonyMock
        );
    }

    /**
     * Test test() delegates to symfony frontend
     */
    public function testTestDelegatesToSymfony(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('test')
            ->with('test_key')
            ->willReturn(1234567890);

        $result = $this->backendWrapper->test('test_key');

        $this->assertEquals(1234567890, $result);
    }

    /**
     * Test test() returns false when cache miss
     */
    public function testTestReturnsFalseOnCacheMiss(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('test')
            ->with('missing_key')
            ->willReturn(false);

        $result = $this->backendWrapper->test('missing_key');

        $this->assertFalse($result);
    }

    /**
     * Test load() delegates to symfony frontend
     */
    public function testLoadDelegatesToSymfony(): void
    {
        $testData = 'cached_data';

        $this->symfonyMock
            ->expects($this->once())
            ->method('load')
            ->with('test_key')
            ->willReturn($testData);

        $result = $this->backendWrapper->load('test_key');

        $this->assertEquals($testData, $result);
    }

    /**
     * Test load() ignores doNotTestCacheValidity parameter
     */
    public function testLoadIgnoresValidityParameter(): void
    {
        $testData = 'cached_data';

        $this->symfonyMock
            ->expects($this->once())
            ->method('load')
            ->with('test_key')
            ->willReturn($testData);

        // doNotTestCacheValidity parameter should be ignored
        $result = $this->backendWrapper->load('test_key', true);

        $this->assertEquals($testData, $result);
    }

    /**
     * Test load() returns false on cache miss
     */
    public function testLoadReturnsFalseOnCacheMiss(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('load')
            ->with('missing_key')
            ->willReturn(false);

        $result = $this->backendWrapper->load('missing_key');

        $this->assertFalse($result);
    }

    /**
     * Test save() delegates to symfony frontend
     */
    public function testSaveDelegatesToSymfony(): void
    {
        $data = 'test_data';
        $id = 'test_key';
        $tags = ['tag1', 'tag2'];
        $lifetime = 3600;

        $this->symfonyMock
            ->expects($this->once())
            ->method('save')
            ->with($data, $id, $tags, $lifetime)
            ->willReturn(true);

        $result = $this->backendWrapper->save($data, $id, $tags, $lifetime);

        $this->assertTrue($result);
    }

    /**
     * Test save() without tags
     */
    public function testSaveWithoutTags(): void
    {
        $data = 'test_data';
        $id = 'test_key';

        $this->symfonyMock
            ->expects($this->once())
            ->method('save')
            ->with($data, $id, [], null)
            ->willReturn(true);

        $result = $this->backendWrapper->save($data, $id);

        $this->assertTrue($result);
    }

    /**
     * Test save() returns false on failure
     */
    public function testSaveReturnsFalseOnFailure(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('save')
            ->willReturn(false);

        $result = $this->backendWrapper->save('data', 'key');

        $this->assertFalse($result);
    }

    /**
     * Test remove() delegates to symfony frontend
     */
    public function testRemoveDelegatesToSymfony(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('remove')
            ->with('test_key')
            ->willReturn(true);

        $result = $this->backendWrapper->remove('test_key');

        $this->assertTrue($result);
    }

    /**
     * Test remove() returns false on failure
     */
    public function testRemoveReturnsFalseOnFailure(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('remove')
            ->with('test_key')
            ->willReturn(false);

        $result = $this->backendWrapper->remove('test_key');

        $this->assertFalse($result);
    }

    /**
     * clean() delegates every mode (and its tags) to the Symfony frontend and returns its result.
     *
     * @param string $mode
     * @param array $tags
     */
    #[DataProvider('cleanModeDataProvider')]
    public function testCleanDelegatesToFrontend(string $mode, array $tags): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('clean')
            ->with($mode, $tags)
            ->willReturn(true);

        $this->assertTrue($this->backendWrapper->clean($mode, $tags));
    }

    /**
     * All cleaning modes supported by the Symfony frontend, matching the Zend backend's coverage.
     *
     * @return array<string, array{0: string, 1: array}>
     */
    public static function cleanModeDataProvider(): array
    {
        return [
            'all' => [CacheConstants::CLEANING_MODE_ALL, []],
            'old' => [CacheConstants::CLEANING_MODE_OLD, []],
            'matchingTag' => [CacheConstants::CLEANING_MODE_MATCHING_TAG, ['tag1']],
            'notMatchingTag' => [CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG, ['tag1']],
            'matchingAnyTag' => [CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['tag1', 'tag2']],
        ];
    }

    /**
     * clean() propagates a false result from the frontend.
     */
    public function testCleanReturnsFrontendFailure(): void
    {
        $this->symfonyMock
            ->expects($this->once())
            ->method('clean')
            ->willReturn(false);

        $this->assertFalse($this->backendWrapper->clean(CacheConstants::CLEANING_MODE_ALL));
    }

    /**
     * Test setDirectives() is a no-op
     */
    public function testSetDirectivesIsNoOp(): void
    {
        // Should not throw any exceptions and Symfony backend options are not stored in the wrapper
        $this->backendWrapper->setDirectives(['lifetime' => 3600]);
        $this->backendWrapper->setDirectives([]);

        // No assertions needed - just verify it doesn't crash
        $this->assertTrue(true);
    }

    /**
     * Test prune() returns false when the underlying pool is not pruneable
     */
    public function testPruneReturnsFalseWhenPoolNotPruneable(): void
    {
        // The plain CacheItemPoolInterface mock does not implement PruneableInterface
        $this->assertFalse($this->backendWrapper->prune());
    }

    /**
     * Test prune() delegates to the pool when it is pruneable
     */
    public function testPruneDelegatesToPruneablePool(): void
    {
        $pruneablePool = $this->createMockForIntersectionOfInterfaces(
            [CacheItemPoolInterface::class, PruneableInterface::class]
        );
        $pruneablePool->expects($this->once())
            ->method('prune')
            ->willReturn(true);

        $backendWrapper = new BackendWrapper($pruneablePool, $this->adapterMock, $this->symfonyMock);

        $this->assertTrue($backendWrapper->prune());
    }

    /**
     * Test clear() clears all indices and cache
     */
    public function testClearClearsAllIndicesAndCache(): void
    {
        $this->adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $result = $this->backendWrapper->clear();

        $this->assertTrue($result);
    }

    /**
     * Test clear() returns false on cache clear failure
     */
    public function testClearReturnsFalseOnFailure(): void
    {
        $this->adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->willReturn(false);

        $result = $this->backendWrapper->clear();

        $this->assertFalse($result);
    }
}
