<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\PackageInfo;

class PackageInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->componentRegistrar = $this->createMock(\Magento\Framework\Component\ComponentRegistrar::class);
        $this->reader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->componentRegistrar->expects($this->once())
            ->method('getPaths')
            ->will($this->returnValue(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E']));

        $composerData = [
            'A/composer.json' => '{"name":"a", "require":{"b":"0.1"}, "conflict":{"c":"0.1"}, "version":"0.1"}',
            'B/composer.json' => '{"name":"b", "require":{"d":"0.3"}, "version":"0.2"}',
            'C/composer.json' => '{"name":"c", "require":{"e":"0.1"}, "version":"0.1"}',
            'D/composer.json' => '{"name":"d", "conflict":{"c":"0.1"}, "version":"0.3"}',
            'E/composer.json' => '{"name":"e", "version":"0.4"}',
        ];
        $fileIteratorMock = $this->createMock(\Magento\Framework\Config\FileIterator::class);
        $fileIteratorMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($composerData));
        $this->reader->expects($this->once())
            ->method('getComposerJsonFiles')
            ->will($this->returnValue($fileIteratorMock));

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($serializedData) {
                    return json_decode($serializedData, true);
                }
            );
        $this->packageInfo = new PackageInfo(
            $this->reader,
            $this->componentRegistrar,
            $this->serializerMock
        );
    }

    public function testGetModuleName()
    {
        $this->assertSame('A', $this->packageInfo->getModuleName('a'));
        $this->assertSame('B', $this->packageInfo->getModuleName('b'));
        $this->assertSame('C', $this->packageInfo->getModuleName('c'));
        $this->assertSame('D', $this->packageInfo->getModuleName('d'));
        $this->assertSame('E', $this->packageInfo->getModuleName('e'));
        $this->assertSame(
            'Magento_TestModuleName',
            $this->packageInfo->getModuleName('magento/module-test-module-name')
        );
        $this->assertArrayHasKey('Magento_TestModuleName', $this->packageInfo->getNonExistingDependencies());
    }

    public function testGetPackageName()
    {
        $this->assertSame('a', $this->packageInfo->getPackageName('A'));
        $this->assertSame('b', $this->packageInfo->getPackageName('B'));
        $this->assertSame('c', $this->packageInfo->getPackageName('C'));
        $this->assertSame('d', $this->packageInfo->getPackageName('D'));
        $this->assertSame('e', $this->packageInfo->getPackageName('E'));
    }

    public function testGetRequireReturnModuleName()
    {
        $this->assertSame(['B'], $this->packageInfo->getRequire('A'));
        $this->assertSame(['D'], $this->packageInfo->getRequire('B'));
        $this->assertSame(['E'], $this->packageInfo->getRequire('C'));
        $this->assertSame([], $this->packageInfo->getRequire('D'));
        $this->assertSame([], $this->packageInfo->getRequire('E'));
    }

    public function testGetConflictReturnModuleName()
    {
        $this->assertSame(['C' => '0.1'], $this->packageInfo->getConflict('A'));
        $this->assertSame([], $this->packageInfo->getConflict('B'));
        $this->assertSame([], $this->packageInfo->getConflict('C'));
        $this->assertSame(['C' => '0.1'], $this->packageInfo->getConflict('D'));
        $this->assertSame([], $this->packageInfo->getConflict('E'));
    }

    public function testGetVersion()
    {
        $this->assertSame('0.1', $this->packageInfo->getVersion('A'));
        $this->assertSame('0.2', $this->packageInfo->getVersion('B'));
        $this->assertSame('0.1', $this->packageInfo->getVersion('C'));
        $this->assertSame('0.3', $this->packageInfo->getVersion('D'));
        $this->assertSame('0.4', $this->packageInfo->getVersion('E'));
        $this->assertSame('', $this->packageInfo->getVersion('F'));
    }

    public function testGetRequiredBy()
    {
        $this->assertSame(['A'], $this->packageInfo->getRequiredBy('b'));
    }
}
