<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

use Zend\Stdlib\Parameters;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var array
     */
    private $serverArray;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        // Stash the $_SERVER array to protect it from modification in test
        $this->serverArray = $_SERVER;
    }

    public function tearDown()
    {
        $_SERVER = $this->serverArray;
    }

    private function getModel($uri = null)
    {
        return new Request($uri);
    }

    public function testSetPathInfoWithNullValue()
    {
        $this->model = $this->getModel();
        $actual = $this->model->setPathInfo();
        $this->assertEquals($this->model, $actual);
    }

    public function testSetPathInfoWithValue()
    {
        $this->model = $this->getModel();
        $expected = 'testPathInfo';
        $this->model->setPathInfo($expected);
        $this->assertEquals($expected, $this->model->getPathInfo());
    }

    public function testSetPathInfoWithQueryPart()
    {
        $uri = 'http://test.com/node?queryValue';
        $this->model = $this->getModel($uri);
        $this->model->setPathInfo();
        $this->assertEquals('/node', $this->model->getPathInfo());
    }

    /**
     * @param string $name
     * @param string $default
     * @param string $result
     * @dataProvider getServerProvider
     */
    public function testGetServer($name, $default, $result)
    {
        $this->model = $this->getModel();
        $this->model->setServer(new Parameters([
            'HTTPS' => 'off',
            'DOCUMENT_ROOT' => '/test',
            'HTTP_ACCEPT' => '',
            'HTTP_CONNECTION' => 'http-connection',
            'HTTP_REFERER' => 'http-referer',
            'HTTP_X_FORWARDED_FOR' => 'x-forwarded',
            'HTTP_USER_AGENT' => 'user-agent',
            'PATH_INFO' => 'path-info',
            'QUERY_STRING' => '',
            'REMOTE_HOST' => 'remote-host',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => 'request-uri',
            'SERVER_NAME' => 'server-name',
        ]));
        $this->assertEquals($result, $this->model->getServer($name, $default));
    }

    /**
     * @return array
     */
    public function getServerProvider()
    {
        return [
            ['HTTPS', '', 'off'],
            ['DOCUMENT_ROOT', '', '/test'],
            ['ORIG_PATH_INFO', 'orig-path-info', 'orig-path-info'],
            ['PATH_INFO', '', 'path-info'],
            ['QUERY_STRING', '', ''],
            ['REMOTE_HOST', 'test', 'remote-host'],
            ['REQUEST_METHOD', '', 'GET'],
            ['REQUEST_URI', 'test', 'request-uri'],
            ['SERVER_NAME', 'test', 'server-name'],

            ['HTTP_ACCEPT', 'http-accept', ''],
            ['HTTP_CONNECTION', '', 'http-connection'],
            ['HTTP_HOST', 'http-host', 'http-host'],
            ['HTTP_REFERER', '', 'http-referer'],
            ['HTTP_USER_AGENT', '', 'user-agent'],
            ['HTTP_X_FORWARDED_FOR', '', 'x-forwarded'],

            ['Accept', 'accept', 'accept'],
            ['Connection', '', ''],
            ['Host', 'http-host', 'http-host'],
            ['Referer', 'referer', 'referer'],
            ['User-Agent', '', ''],
            ['X-Forwarded-For', 'test', 'test'],
        ];
    }
}
