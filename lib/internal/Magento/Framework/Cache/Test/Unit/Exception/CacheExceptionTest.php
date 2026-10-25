<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Exception;

use Magento\Framework\Cache\Exception\CacheException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

/**
 * Test for CacheException
 */
class CacheExceptionTest extends TestCase
{
    /**
     * Test exception can be instantiated with Phrase
     *
     * @return void
     */
    public function testExceptionCanBeInstantiatedWithPhrase(): void
    {
        $message = new Phrase('Cache error occurred');
        $exception = new CacheException($message);

        $this->assertInstanceOf(CacheException::class, $exception);
        $this->assertInstanceOf(LocalizedException::class, $exception);
        $this->assertEquals('Cache error occurred', $exception->getMessage());
    }

    /**
     * Test exception with parameters
     *
     * @return void
     */
    public function testExceptionWithParameters(): void
    {
        $message = new Phrase('Cache error for key %1', ['test_key']);
        $exception = new CacheException($message);

        $this->assertEquals('Cache error for key test_key', $exception->getMessage());
    }

    /**
     * Test exception with previous exception
     *
     * @return void
     */
    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = new Phrase('Cache exception');
        $exception = new CacheException($message, $previous);

        $this->assertInstanceOf(CacheException::class, $exception);
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test exception can be thrown and caught
     *
     * @return void
     */
    public function testExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Test cache exception');

        throw new CacheException(new Phrase('Test cache exception'));
    }

    /**
     * Test exception with empty message
     *
     * @return void
     */
    public function testExceptionWithEmptyMessage(): void
    {
        $message = new Phrase('');
        $exception = new CacheException($message);

        $this->assertInstanceOf(CacheException::class, $exception);
        $this->assertEquals('', $exception->getMessage());
    }

    /**
     * Test exception message is translatable
     *
     * @return void
     */
    public function testExceptionMessageIsTranslatable(): void
    {
        $phrase = new Phrase('Invalid cache mode: %1', ['test_mode']);
        $exception = new CacheException($phrase);

        $this->assertStringContainsString('test_mode', $exception->getMessage());
    }

    /**
     * Test exception extends LocalizedException
     *
     * @return void
     */
    public function testExceptionExtendsLocalizedException(): void
    {
        $exception = new CacheException(new Phrase('Test'));

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(LocalizedException::class, $exception);
    }

    /**
     * Test exception with special characters
     *
     * @return void
     */
    public function testExceptionWithSpecialCharacters(): void
    {
        $message = new Phrase('Cache error: <tag> & "quotes" \'apostrophe\'');
        $exception = new CacheException($message);

        $this->assertStringContainsString('<tag>', $exception->getMessage());
        $this->assertStringContainsString('&', $exception->getMessage());
    }

    /**
     * Test exception code
     *
     * @return void
     */
    public function testExceptionCode(): void
    {
        $previous = new \RuntimeException('Previous', 123);
        $exception = new CacheException(new Phrase('Test'), $previous);

        // LocalizedException doesn't use numeric codes, it uses 0
        $this->assertEquals(0, $exception->getCode());
    }

    /**
     * Test multiple parameters in phrase
     *
     * @return void
     */
    public function testMultipleParametersInPhrase(): void
    {
        $phrase = new Phrase(
            'Cache operation %1 failed for key %2 with mode %3',
            ['save', 'cache_key', 'CLEANING_MODE_ALL']
        );
        $exception = new CacheException($phrase);

        $message = $exception->getMessage();
        $this->assertStringContainsString('save', $message);
        $this->assertStringContainsString('cache_key', $message);
        $this->assertStringContainsString('CLEANING_MODE_ALL', $message);
    }
}
