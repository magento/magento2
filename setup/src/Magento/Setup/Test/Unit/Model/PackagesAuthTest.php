<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\PackagesAuth;

/**
 * Tests Magento\Setup\Model\PackagesAuth
 */
class PackagesAuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var PackagesAuth
     */
    private $packagesAuth;

    public function setUp()
    {
        $zendServiceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', [], [], '', false);
        $zendServiceLocator
            ->expects($this->any())
            ->method('get')
            ->with('config')
            ->willReturn([
                'marketplace' => [
                    'check_credentials_url' => 'some_url'
                ]
            ]);
        $this->curl = $this->getMock('Magento\Framework\HTTP\Client\Curl', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->packagesAuth = new PackagesAuth($zendServiceLocator, $this->curl, $this->filesystem);
    }

    public function testCheckCredentialsActionBadCredentials()
    {
        $this->curl->expects($this->once())->method('setCredentials')->with('username', 'password');
        $this->curl->expects($this->once())->method('getStatus');
        $expectedValue = '{"success":false,"message":"Bad credentials"}';
        $returnValue = $this->packagesAuth->checkCredentials('username', 'password');
        $this->assertSame($expectedValue, $returnValue);
    }

    public function testCheckCredentials()
    {
        $this->curl->expects($this->once())->method('setCredentials')->with('username', 'password');
        $this->curl->expects($this->once())->method('getStatus')->willReturn(200);
        $this->curl->expects($this->once())->method('getBody')->willReturn("{'someJson'}");
        $directory = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($directory));
        $directory->expects($this->once())
            ->method('writeFile')
            ->with(PackagesAuth::PATH_TO_PACKAGES_FILE, "{'someJson'}");
        $expectedValue = '{"success":true}';
        $returnValue = $this->packagesAuth->checkCredentials('username', 'password');
        $this->assertSame($expectedValue, $returnValue);
    }

    public function testCheckCredentialsActionWithException()
    {
        $this->curl->expects($this->once())->method('setCredentials')->with('username', 'password');
        $this->curl->expects($this->once())
            ->method('getStatus')
            ->will($this->throwException(new \Exception("Test error")));
        $this->curl->expects($this->never())->method('getBody')->willReturn("{'someJson}");

        $expectedValue = '{"success":false,"message":"Test error"}';
        $returnValue = $this->packagesAuth->checkCredentials('username', 'password');
        $this->assertSame($expectedValue, $returnValue);
    }

    public function testRemoveCredentials()
    {
        $directoryWrite = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $directoryRead = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->will($this->returnValue($directoryRead));
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($directoryWrite));
        $directoryWrite->expects($this->once())->method('isExist')->willReturn(true);
        $directoryWrite->expects($this->once())->method('isReadable')->willReturn(true);
        $directoryWrite->expects($this->once())->method('delete')->willReturn(true);
        $directoryRead->expects($this->once())->method('isExist')->willReturn(true);
        $directoryRead->expects($this->once())->method('isReadable')->willReturn(true);
        $directoryRead->expects($this->once())
            ->method('ReadFile')
            ->willReturn('{"http-basic":{"some_url":{"username":"somename","password":"somepassword"}}}');

        $this->assertTrue($this->packagesAuth->removeCredentials());
    }

    public function testSaveAuthJson()
    {
        $directoryWrite = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($directoryWrite));
        $directoryWrite->expects($this->once())->method('writeFile')->willReturn(true);

        $this->assertTrue($this->packagesAuth->saveAuthJson("testusername", "testpassword"));
    }
}
