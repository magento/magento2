<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Setup\Model\PackagesAuth;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Setup\Model\PackagesAuth
 */
class PackagesAuthTest extends TestCase
{
    /**
     * @var MockObject|Curl
     */
    private $curl;

    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;

    /**
     * @var PackagesAuth
     */
    private $packagesAuth;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $laminasServiceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $laminasServiceLocator
            ->expects($this->any())
            ->method('get')
            ->with('config')
            ->willReturn([
                'marketplace' => [
                    'check_credentials_url' => 'some_url'
                ]
            ]);
        $this->curl = $this->createMock(Curl::class, [], [], '', false);
        $this->filesystem = $this->createMock(Filesystem::class, [], [], '', false);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($serializedData) {
                    return json_encode($serializedData);
                }
            );
        $this->packagesAuth = new PackagesAuth(
            $laminasServiceLocator,
            $this->curl,
            $this->filesystem,
            $this->serializerMock
        );
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
        $directory = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($directory);
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
            ->willThrowException(new \Exception("Test error"));
        $this->curl->expects($this->never())->method('getBody')->willReturn("{'someJson}");

        $expectedValue = '{"success":false,"message":"Test error"}';
        $returnValue = $this->packagesAuth->checkCredentials('username', 'password');
        $this->assertSame($expectedValue, $returnValue);
    }

    public function testRemoveCredentials()
    {
        $directoryWrite = $this->getMockForAbstractClass(WriteInterface::class);
        $directoryRead = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($directoryRead);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($directoryWrite);
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
        $directoryWrite = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($directoryWrite);
        $directoryWrite->expects($this->once())->method('writeFile')->willReturn(true);

        $this->assertTrue($this->packagesAuth->saveAuthJson("testusername", "testpassword"));
    }
}
