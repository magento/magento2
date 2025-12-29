<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Test\Unit\Model\Service;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\CompareListGraphQl\Model\Service\CompareCookieManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for CompareCookieManager
 */
class CompareCookieManagerTest extends TestCase
{
    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManagerMock;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $cookieMetadataFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var PublicCookieMetadata|MockObject
     */
    private $publicCookieMetadataMock;

    /**
     * @var CompareCookieManager
     */
    private $compareCookieManager;

    /**
     * Set up test environment
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(CookieMetadataFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->publicCookieMetadataMock = $this->createMock(PublicCookieMetadata::class);

        $this->compareCookieManager = new CompareCookieManager(
            $this->cookieManagerMock,
            $this->cookieMetadataFactoryMock,
            $this->loggerMock
        );
    }

    /**
     * Test invalidate method successfully sets cookie
     *
     * @return void
     */
    public function testInvalidateSuccess(): void
    {
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($this->publicCookieMetadataMock);

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->with(CompareCookieManager::COOKIE_LIFETIME)
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with(CompareCookieManager::COOKIE_PATH)
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(false)
            ->willReturnSelf();

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                CompareCookieManager::COOKIE_COMPARE_PRODUCTS,
                $this->callback(function ($value) {
                    $decodedValue = json_decode($value, true);
                    return isset($decodedValue['compare-products']) && is_int($decodedValue['compare-products']);
                }),
                $this->publicCookieMetadataMock
            );

        $this->compareCookieManager->invalidate();
    }

    /**
     * Test invalidate method logs exception when cookie setting fails
     *
     * @return void
     */
    public function testInvalidateWithException(): void
    {
        $exception = new InputException(__('Error setting cookie'));

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($this->publicCookieMetadataMock);

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->willReturnSelf();

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error invalidating compare products cookie'));

        $this->compareCookieManager->invalidate();
    }

    /**
     * Test cookie creation with CookieSizeLimitReachedException
     *
     * @return void
     */
    public function testInvalidateWithCookieSizeLimitReachedException(): void
    {
        $exception = new CookieSizeLimitReachedException(__('Cookie size limit reached'));

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($this->publicCookieMetadataMock);

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->willReturnSelf();

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error invalidating compare products cookie'));

        $this->compareCookieManager->invalidate();
    }

    /**
     * Test cookie creation with FailureToSendException
     *
     * @return void
     */
    public function testInvalidateWithFailureToSendException(): void
    {
        $exception = new FailureToSendException(__('Failed to send cookie'));

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($this->publicCookieMetadataMock);

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        $this->publicCookieMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->willReturnSelf();

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error invalidating compare products cookie'));

        $this->compareCookieManager->invalidate();
    }

    /**
     * Test that constants have the expected values
     *
     * @return void
     */
    public function testConstants(): void
    {
        $this->assertEquals('section_data_ids', CompareCookieManager::COOKIE_COMPARE_PRODUCTS);
        $this->assertEquals('/', CompareCookieManager::COOKIE_PATH);
        $this->assertEquals(86400, CompareCookieManager::COOKIE_LIFETIME);
    }
}
