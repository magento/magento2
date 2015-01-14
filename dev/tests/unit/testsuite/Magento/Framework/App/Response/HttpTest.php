<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

use Magento\Framework\Stdlib\Cookie\CookieMetadata;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Http\Context
     */
    protected $contextMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        )->disableOriginalConstructor()->getMock();
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Http\Context')->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Framework\App\Response\Http',
            [
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'context' => $this->contextMock
            ]
        );
        $this->model->headersSentThrowsException = false;
        $this->model->setHeader('name', 'value');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetHeaderWhenHeaderNameIsEqualsName()
    {
        $expected = ['name' => 'Name', 'value' => 'value', 'replace' => false];
        $actual = $this->model->getHeader('Name');
        $this->assertEquals($expected, $actual);
    }

    public function testGetHeaderWhenHeaderNameIsNotEqualsName()
    {
        $this->assertFalse($this->model->getHeader('Test'));
    }

    public function testSendVary()
    {
        $data = ['some-vary-key' => 'some-vary-value'];
        $expectedCookieName = Http::COOKIE_VARY_STRING;
        $expectedCookieValue = sha1(serialize($data));
        $sensitiveCookieMetadataMock = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $sensitiveCookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->will($this->returnSelf());

        $this->contextMock->expects($this->once())
            ->method('getData')
            ->with()
            ->will(
                $this->returnValue($data)
            );

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createSensitiveCookieMetadata')
            ->with()
            ->will(
                $this->returnValue($sensitiveCookieMetadataMock)
            );

        $this->cookieManagerMock->expects($this->once())
            ->method('setSensitiveCookie')
            ->with($expectedCookieName, $expectedCookieValue, $sensitiveCookieMetadataMock);
        $this->model->sendVary();
    }

    public function testSendVaryEmptyData()
    {
        $expectedCookieName = Http::COOKIE_VARY_STRING;
        $cookieMetadataMock = $this->getMock('Magento\Framework\Stdlib\Cookie\CookieMetadata');
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->will($this->returnSelf());

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createCookieMetadata')
            ->with()
            ->will($this->returnValue($cookieMetadataMock));

        $this->cookieManagerMock->expects($this->once())
            ->method('deleteCookie')
            ->with($expectedCookieName, $cookieMetadataMock);
        $this->model->sendVary();
    }

    /**
     * Test setting public cache headers
     */
    public function testSetPublicHeaders()
    {
        $ttl = 120;
        $pragma = 'cache';
        $cacheControl = 'public, max-age=' . $ttl . ', s-maxage=' . $ttl;
        $between = 1000;

        $this->model->setPublicHeaders($ttl);
        $this->assertEquals($pragma, $this->model->getHeader('Pragma')['value']);
        $this->assertEquals($cacheControl, $this->model->getHeader('Cache-Control')['value']);
        $expiresResult = time($this->model->getHeader('Expires')['value']);
        $this->assertTrue($expiresResult > $between || $expiresResult < $between);
    }

    /**
     * Test for setting public headers without time to live parameter
     */
    public function testSetPublicHeadersWithoutTtl()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Time to live is a mandatory parameter for set public headers'
        );
        $this->model->setPublicHeaders(null);
    }

    /**
     * Test setting public cache headers
     */
    public function testSetPrivateHeaders()
    {
        $ttl = 120;
        $pragma = 'cache';
        $cacheControl = 'private, max-age=' . $ttl;
        $expires = gmdate('D, d M Y H:i:s T', strtotime('+' . $ttl . ' seconds'));

        $this->model->setPrivateHeaders($ttl);
        $this->assertEquals($pragma, $this->model->getHeader('Pragma')['value']);
        $this->assertEquals($cacheControl, $this->model->getHeader('Cache-Control')['value']);
        $this->assertEquals($expires, $this->model->getHeader('Expires')['value']);
    }

    /**
     * Test for setting public headers without time to live parameter
     */
    public function testSetPrivateHeadersWithoutTtl()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Time to live is a mandatory parameter for set private headers'
        );
        $this->model->setPrivateHeaders(null);
    }

    /**
     * Test setting public cache headers
     */
    public function testSetNoCacheHeaders()
    {
        $pragma = 'no-cache';
        $cacheControl = 'no-store, no-cache, must-revalidate, max-age=0';
        $expires = gmdate('D, d M Y H:i:s T', strtotime('-1 year'));

        $this->model->setNoCacheHeaders();
        $this->assertEquals($pragma, $this->model->getHeader('Pragma')['value']);
        $this->assertEquals($cacheControl, $this->model->getHeader('Cache-Control')['value']);
        $this->assertEquals($expires, $this->model->getHeader('Expires')['value']);
    }

    /**
     * Test setting body in JSON format
     */
    public function testRepresentJson()
    {
        $this->model->setHeader('Content-Type', 'text/javascript');
        $this->model->representJson('json_string');
        $this->assertEquals('application/json', $this->model->getHeader('Content-Type')['value']);
        $this->assertEquals('json_string', $this->model->getBody('default'));
    }

    /**
     * Test for getHeader method
     *
     * @dataProvider headersDataProvider
     * @covers       \Magento\Framework\App\Response\Http::getHeader
     * @param string $header
     */
    public function testGetHeaderExists($header)
    {
        $this->model->setHeader($header['name'], $header['value'], $header['replace']);
        $this->assertEquals($header, $this->model->getHeader($header['name']));
    }

    /**
     * Data provider for testGetHeader
     *
     * @return array
     */
    public function headersDataProvider()
    {
        return [
            [['name' => 'X-Frame-Options', 'value' => 'SAMEORIGIN', 'replace' => true]],
            [['name' => 'Test2', 'value' => 'Test2', 'replace' => false]]
        ];
    }

    /**
     * Test for getHeader method. Validation for attempt to get not existing header
     *
     * @covers \Magento\Framework\App\Response\Http::getHeader
     */
    public function testGetHeaderNotExists()
    {
        $this->model->setHeader('Name', 'value', true);
        $this->assertFalse($this->model->getHeader('Wrong name'));
    }

    /**
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ObjectManager isn't initialized
     */
    public function testWakeUpWithException()
    {
        /* ensure that the test preconditions are met */
        $objectManagerClass = new \ReflectionClass('Magento\Framework\App\ObjectManager');
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
        $objectManagerMock = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Stdlib\CookieManagerInterface')
            ->will($this->returnValue($this->cookieManagerMock));

        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory')
            ->will($this->returnValue($this->cookieMetadataFactoryMock));

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $this->model->__wakeup();
    }
}
