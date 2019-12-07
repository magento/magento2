<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Response;

use Magento\Framework\App\Response\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HttpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Http
     */
    protected $model;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionConfigMock;

    /**
     * @var int
     */
    private $cookieLifeTime = 3600;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->contextMock = $this->getMockBuilder(
            \Magento\Framework\App\Http\Context::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(
            \Magento\Framework\App\Response\Http::class,
            [
                'request' => $this->requestMock,
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'context' => $this->contextMock,
                'dateTime' => $this->dateTimeMock,
                'sessionConfig' => $this->sessionConfigMock,
            ]
        );
        $this->model->headersSentThrowsException = false;
        $this->model->setHeader('Name', 'Value');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        unset($this->model);
        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock*/
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    public function testSendVary()
    {
        $expectedCookieName = Http::COOKIE_VARY_STRING;
        $expectedCookieValue = 'SHA1 Serialized String';
        $sensitiveCookieMetadataMock = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $sensitiveCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->will($this->returnSelf());

        $this->contextMock->expects($this->once())
            ->method('getVaryString')
            ->will($this->returnValue($expectedCookieValue));

        $this->sessionConfigMock->expects($this->once())
            ->method('getCookieLifetime')
            ->willReturn($this->cookieLifeTime);

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createSensitiveCookieMetadata')
            ->with([CookieMetadata::KEY_DURATION => $this->cookieLifeTime])
            ->willReturn($sensitiveCookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setSensitiveCookie')
            ->with($expectedCookieName, $expectedCookieValue, $sensitiveCookieMetadataMock);
        $this->model->sendVary();
    }

    public function testSendVaryEmptyDataDeleteCookie()
    {
        $expectedCookieName = Http::COOKIE_VARY_STRING;
        $cookieMetadataMock = $this->createMock(\Magento\Framework\Stdlib\Cookie\CookieMetadata::class);
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->will($this->returnSelf());
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

    public function testSendVaryEmptyData()
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
     * Test setting public cache headers
     */
    public function testSetPublicHeaders()
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
     * Test for setting public headers without time to live parameter
     */
    public function testSetPublicHeadersWithoutTtl()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Time to live is a mandatory parameter for set public headers');
        $this->model->setPublicHeaders(null);
    }

    /**
     * Test setting public cache headers
     */
    public function testSetPrivateHeaders()
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
     * Test for setting public headers without time to live parameter
     */
    public function testSetPrivateHeadersWithoutTtl()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Time to live is a mandatory parameter for set private headers');
        $this->model->setPrivateHeaders(null);
    }

    /**
     * Test setting public cache headers
     */
    public function testSetNoCacheHeaders()
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
     * Test setting body in JSON format
     */
    public function testRepresentJson()
    {
        $this->model->setHeader('Content-Type', 'text/javascript');
        $this->model->representJson('json_string');
        $this->assertEquals('application/json', $this->model->getHeader('Content-Type')->getFieldValue());
        $this->assertEquals('json_string', $this->model->getBody('default'));
    }

    /**
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ObjectManager isn't initialized
     */
    public function testWakeUpWithException()
    {
        /* ensure that the test preconditions are met */
        $objectManagerClass = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
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
     * @covers \Magento\Framework\App\Response\Http::__wakeup
     */
    public function testWakeUpWith()
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Stdlib\CookieManagerInterface::class)
            ->will($this->returnValue($this->cookieManagerMock));
        $objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class)
            ->will($this->returnValue($this->cookieMetadataFactoryMock));

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $this->model->__wakeup();
    }

    public function testSetXFrameOptions()
    {
        $value = 'DENY';
        $this->model->setXFrameOptions($value);
        $this->assertSame($value, $this->model->getHeader(Http::HEADER_X_FRAME_OPT)->getFieldValue());
    }
}
