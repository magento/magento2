<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use InvalidArgumentException;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\BackendWrapper;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

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
     * Test clean() with 'all' mode
     */
    public function testCleanWithAllMode(): void
    {
        $this->adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $result = $this->backendWrapper->clean('all');

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_ALL constant
     */
    public function testCleanWithAllModeConstant(): void
    {
        $this->adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $result = $this->backendWrapper->clean(CacheConstants::CLEANING_MODE_ALL);

        $this->assertTrue($result);
    }

    /**
     * Test clean() with 'old' mode
     */
    public function testCleanWithOldMode(): void
    {
        // 'old' mode is a no-op (returns true without doing anything)
        $this->adapterMock
            ->expects($this->never())
            ->method('clearAllIndices');

        $this->cacheMock
            ->expects($this->never())
            ->method('clear');

        $result = $this->backendWrapper->clean('old');

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_OLD constant
     */
    public function testCleanWithOldModeConstant(): void
    {
        $result = $this->backendWrapper->clean(CacheConstants::CLEANING_MODE_OLD);

        $this->assertTrue($result);
    }

    /**
     * Test clean() with unsupported mode throws exception
     */
    public function testCleanWithUnsupportedModeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Backend clean only supports ALL and OLD modes");

        $this->backendWrapper->clean('unsupported_mode');
    }

    /**
     * Test clean() with CLEANING_MODE_MATCHING_TAG throws exception
     */
    public function testCleanWithMatchingTagModeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Backend clean only supports ALL and OLD modes");

        $this->backendWrapper->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, ['tag1']);
    }

    /**
     * Test setOption() is a no-op
     */
    public function testSetOptionIsNoOp(): void
    {
        // Should not throw any exceptions
        $this->backendWrapper->setOption('some_option', 'some_value');
        $this->backendWrapper->setOption('another_option', 123);

        // No assertions needed - just verify it doesn't crash
        $this->assertTrue(true);
    }

    /**
     * Test getOption() returns null for any option
     */
    public function testGetOptionReturnsNull(): void
    {
        $result1 = $this->backendWrapper->getOption('any_option');
        $result2 = $this->backendWrapper->getOption('another_option');

        $this->assertNull($result1);
        $this->assertNull($result2);
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
