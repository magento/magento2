<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache;

class ManagerAppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var \Magento\Framework\App\Console\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    protected function setUp()
    {
        $this->cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $this->cacheManager->expects($this->any())
            ->method('getAvailableTypes')
            ->will($this->returnValue(['foo', 'bar', 'baz']));
        $this->cacheManager->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(['foo' => true, 'bar' => true, 'baz' => false]));
        $this->response = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
    }

    public function testLaunchStatus()
    {
        $requestArgs = [
            ManagerApp::KEY_STATUS => true
        ];

        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Current status:%afoo: 1%abar: 1%abaz: 0")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testLaunchEnable()
    {
        $requestArgs = [
            ManagerApp::KEY_SET => true,
            ManagerApp::KEY_TYPES => 'foo,,bar, baz,',
        ];
        $this->cacheManager->expects($this->once())
            ->method('setEnabled')
            ->with(['foo', 'bar', 'baz'], true)
            ->will($this->returnValue(['baz']));
        $this->cacheManager->expects($this->once())
            ->method('clean')
            ->with(['baz']);
        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Changed cache status:\n%abaz: 0 -> 1\nCleaned cache types:\nbaz")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testLaunchDisable()
    {
        $requestArgs = [
            ManagerApp::KEY_SET => false,
            ManagerApp::KEY_TYPES => 'foo,,bar, baz,',
        ];
        $this->cacheManager->expects($this->once())
            ->method('setEnabled')
            ->with(['foo', 'bar', 'baz'], false)
            ->will($this->returnValue(['baz']));
        $this->cacheManager->expects($this->never())
            ->method('clean');
        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Changed cache status:\n%abaz: 1 -> 0\n")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testLaunchFlush()
    {
        $requestArgs = [
            ManagerApp::KEY_FLUSH => true,
            ManagerApp::KEY_TYPES => 'foo,bar',
        ];
        $this->cacheManager->expects($this->never())
            ->method('setEnabled');
        $this->cacheManager->expects($this->once())
            ->method('flush')
            ->with(['foo', 'bar']);
        $this->cacheManager->expects($this->never())
            ->method('clean');
        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Flushed cache types:\nfoo\nbar")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testLaunchClean()
    {
        $requestArgs = [
            ManagerApp::KEY_CLEAN => true,
            ManagerApp::KEY_TYPES => 'foo,bar',
        ];
        $this->cacheManager->expects($this->never())
            ->method('setEnabled');
        $this->cacheManager->expects($this->never())
            ->method('flush');
        $this->cacheManager->expects($this->once())
            ->method('clean')
            ->with(['foo', 'bar']);
        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Cleaned cache types:\nfoo\nbar")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testLaunchSetAndClean()
    {
        $requestArgs = [
            ManagerApp::KEY_SET => true,
            ManagerApp::KEY_CLEAN => true,
            ManagerApp::KEY_TYPES => 'foo,bar',
        ];
        $this->cacheManager->expects($this->once())
            ->method('setEnabled')
            ->with(['foo', 'bar'], true)
            ->will($this->returnValue(['foo']));
        $this->cacheManager->expects($this->never())
            ->method('flush');
        $this->cacheManager->expects($this->once())
            ->method('clean')
            ->with(['foo', 'bar']);
        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Changed cache status:\n%afoo: 0 -> 1\nCleaned cache types:\nfoo\nbar")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testLaunchAll()
    {
        $requestArgs = [
            ManagerApp::KEY_SET => true,
            ManagerApp::KEY_FLUSH => true,
            ManagerApp::KEY_CLEAN => true,
            ManagerApp::KEY_TYPES => 'foo,baz',
        ];
        $this->cacheManager->expects($this->once())
            ->method('setEnabled')
            ->with(['foo', 'baz'], true)
            ->will($this->returnValue(['baz']));
        $this->cacheManager->expects($this->once())
            ->method('flush')
            ->with(['foo', 'baz']);
        $this->cacheManager->expects($this->never())
            ->method('clean');
        $this->response->expects($this->once())
            ->method('setBody')
            ->with(
                $this->matches("Changed cache status:\n%abaz: 0 -> 1%aFlushed cache types:\nfoo\nbaz")
            );

        $model = new ManagerApp($this->cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following requested cache types are not supported: 'unsupported', 'wrong'
     */
    public function testLaunchWithUnsupportedCacheTypes()
    {
        $requestArgs = [
            ManagerApp::KEY_SET => true,
            ManagerApp::KEY_TYPES => 'foo,unsupported,wrong,bar',
        ];
        $cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $cacheManager->expects($this->any())
            ->method('getAvailableTypes')
            ->will($this->returnValue(['foo', 'bar', 'baz']));
        $cacheManager->expects($this->never())
            ->method('setEnabled');

        $model = new ManagerApp($cacheManager, $this->response, $requestArgs);
        $model->launch();
    }

    public function testCatchException()
    {
        $exceptionMessage = 'Exception message';
        $model = new ManagerApp($this->cacheManager, $this->response, []);
        $this->response->expects($this->once())
            ->method('setBody')
            ->with($exceptionMessage);
        $this->assertFalse($model->catchException(
            $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false),
            new \Exception($exceptionMessage)
        ));
    }
}
