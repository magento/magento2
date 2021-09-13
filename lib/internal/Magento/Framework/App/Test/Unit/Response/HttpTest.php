<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HttpTest extends TestCase
{
    /**
     * @var Http
     */
    protected $model;

    /**
     * @var CookieManagerInterface|MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Context|MockObject
     */
    protected $dateTimeMock;

    /**
     * @var RequestHttp|MockObject
     */
    protected $requestMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $sessionConfigMock;

    /**
     * @var int
     */
    private $cookieLifeTime = 3600;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(RequestHttp::class)
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(CookieMetadataFactory::class)
            ->addMethods(['get'])
            ->onlyMethods(['createSensitiveCookieMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(
            Http::class,
            [
                'request' => $this->requestMock,
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'context' => $this->contextMock,
                'dateTime' => $this->dateTimeMock,
                'sessionConfig' => $this->sessionConfigMock
            ]
        );
        $this->model->headersSentThrowsException = false;
        $this->model->setHeader('Name', 'Value');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->model);
        /** @var ObjectManagerInterface|MockObject $objectManagerMock*/
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        AppObjectManager::setInstance($objectManagerMock);
    }

    /**
     * @return void
     */
    public function testSendVary(): void
    {
        $expectedCookieName = Http::COOKIE_VARY_STRING;
        $expectedCookieValue = 'SHA1 Serialized String';
        $sensitiveCookieMetadataMock = $this->getMockBuilder(
            SensitiveCookieMetadata::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $sensitiveCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/')->willReturnSelf();

        $this->contextMock->expects($this->once())
            ->method('getVaryString')
            ->willReturn($expectedCookieValue);

        $this->sessionConfigMock->expects($this->once())
            ->method('getCookieLifetime')
            ->willReturn($this->cookieLifeTime);

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createSensitiveCookieMetadata')
            ->with(
                [
                    CookieMetadata::KEY_DURATION => $this->cookieLifeTime,
                    CookieMetadata::KEY_SAME_SITE => 'Lax'
                ]
            )
            ->willReturn($sensitiveCookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setSensitiveCookie')
            ->with($expectedCookieName, $expectedCookieValue, $sensitiveCookieMetadataMock);
        $this->model->sendVary();
    }

    /**
     * @return void
     */
    public function testSendVaryEmptyDataDeleteCookie(): void
    {
        $expectedCookieName = Http::COOKIE_VARY_STRING;
        $cookieMetadataMock = $this->createMock(CookieMetadata::class);
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/')->willReturnSelf();
        $this->contextMock->expects($this->once())
            ->method('getVaryString')
            ->willReturn(null);
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createSensitiveCookieMetadata')
            ->willReturn($cookieMetadataMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('deleteCookie')
            ->with($expectedCookieName, $cookieMetadataMock);
        $this->requestMock->expects($this->once())
            ->method('get')
            ->willReturn('value');
        $this->model->sendVary();
    }

    /**
     * @return void
     */
    public function testSendVaryEmptyData(): void
    {
        $this->contextMock->expects($this->once())
            ->method('getVaryString')
            ->willReturn(null);
        $this->cookieMetadataFactoryMock->expects($this->never())
            ->method('createSensitiveCookieMetadata');
        $this->requestMock->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->model->sendVary();
    }

    /**
     * Test setting public cache headers.
     *
     * @return void
     */
    public function testSetPublicHeaders(): void
    {
        $ttl = 120;
        $timestamp = 1000000;
        $pragma = 'cache';
        $cacheControl = 'max-age=' . $ttl . ', public, s-maxage=' . $ttl;
        $expiresResult ='Thu, 01 Jan 1970 00:00:00 GMT';

        $this->dateTimeMock->expects($this->once())
            ->method('strToTime')
            ->with('+' . $ttl . ' seconds')
            ->willReturn($timestamp);
        $this->dateTimeMock->expects($this->once())
            ->method('gmDate')
            ->with(Http::EXPIRATION_TIMESTAMP_FORMAT, $timestamp)
            ->willReturn($expiresResult);

        $this->model->setPublicHeaders($ttl);
        $this->assertEquals($pragma, $this->model->getHeader('Pragma')->getFieldValue());
        $this->assertEquals($cacheControl, $this->model->getHeader('Cache-Control')->getFieldValue());
        $this->assertSame($expiresResult, $this->model->getHeader('Expires')->getFieldValue());
    }

    /**
     * Test for setting public headers without time to live parameter.
     *
     * @return void
     */
    public function testSetPublicHeadersWithoutTtl(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Time to live is a mandatory parameter for set public headers');
        $this->model->setPublicHeaders(null);
    }

    /**
     * Test setting public cache headers.
     *
     * @return void
     */
    public function testSetPrivateHeaders(): void
    {
        $ttl = 120;
        $timestamp = 1000000;
        $pragma = 'cache';
        $cacheControl = 'max-age=' . $ttl . ', private';
        $expiresResult ='Thu, 01 Jan 1970 00:00:00 GMT';

        $this->dateTimeMock->expects($this->once())
            ->method('strToTime')
            ->with('+' . $ttl . ' seconds')
            ->willReturn($timestamp);
        $this->dateTimeMock->expects($this->once())
            ->method('gmDate')
            ->with(Http::EXPIRATION_TIMESTAMP_FORMAT, $timestamp)
            ->willReturn($expiresResult);

        $this->model->setPrivateHeaders($ttl);
        $this->assertEquals($pragma, $this->model->getHeader('Pragma')->getFieldValue());
        $this->assertEquals($cacheControl, $this->model->getHeader('Cache-Control')->getFieldValue());
        $this->assertEquals($expiresResult, $this->model->getHeader('Expires')->getFieldValue());
    }

    /**
     * Test for setting public headers without time to live parameter.
     *
     * @return void
     */
    public function testSetPrivateHeadersWithoutTtl(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Time to live is a mandatory parameter for set private headers');
        $this->model->setPrivateHeaders(null);
    }

    /**
     * Test setting public cache headers.
     *
     * @return void
     */
    public function testSetNoCacheHeaders(): void
    {
        $timestamp = 1000000;
        $pragma = 'no-cache';
        $cacheControl = 'max-age=0, must-revalidate, no-cache, no-store';
        $expiresResult ='Thu, 01 Jan 1970 00:00:00 GMT';

        $this->dateTimeMock->expects($this->once())
            ->method('strToTime')
            ->with('-1 year')
            ->willReturn($timestamp);
        $this->dateTimeMock->expects($this->once())
            ->method('gmDate')
            ->with(Http::EXPIRATION_TIMESTAMP_FORMAT, $timestamp)
            ->willReturn($expiresResult);

        $this->model->setNoCacheHeaders();
        $this->assertEquals($pragma, $this->model->getHeader('Pragma')->getFieldValue());
        $this->assertEquals($cacheControl, $this->model->getHeader('Cache-Control')->getFieldValue());
        $this->assertEquals($expiresResult, $this->model->getHeader('Expires')->getFieldValue());
    }

    /**
     * Test setting body in JSON format.
     *
     * @return void
     */
    public function testRepresentJson(): void
    {
        $this->model->setHeader('Content-Type', 'text/javascript');
        $this->model->representJson('json_string');
        $this->assertEquals('application/json', $this->model->getHeader('Content-Type')->getFieldValue());
        $this->assertEquals('json_string', $this->model->getBody());
    }

    /**
     * @return void
     */
    public function testWakeUpWithException(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('ObjectManager isn\'t initialized');
        /* ensure that the test preconditions are met */
        $objectManagerClass = new ReflectionClass(AppObjectManager::class);
        $instanceProperty = $objectManagerClass->getProperty('_instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null);

        $this->model->__wakeup();
        $this->assertNull($this->cookieMetadataFactoryMock);
        $this->assertNull($this->cookieManagerMock);
    }

    /**
     * Test for the magic method __wakeup
     *
     * @return void
     * @covers \Magento\Framework\App\Response\Http::__wakeup
     */
    public function testWakeUpWith(): void
    {
        $objectManagerMock = $this->createMock(AppObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(CookieManagerInterface::class)
            ->willReturn($this->cookieManagerMock);
        $this->cookieMetadataFactoryMock
            ->method('get')
            ->with(CookieMetadataFactory::class)
            ->willReturn($this->cookieMetadataFactoryMock);

        AppObjectManager::setInstance($objectManagerMock);
        $this->model->__wakeup();
    }

    /**
     * @return void
     */
    public function testSetXFrameOptions(): void
    {
        $value = 'DENY';
        $this->model->setXFrameOptions($value);
        $this->assertSame($value, $this->model->getHeader(Http::HEADER_X_FRAME_OPT)->getFieldValue());
    }
}
