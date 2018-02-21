<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\ModuleList;
use \Magento\Framework\Module\PackageInfo;

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
     * @var \Magento\Framework\Serialize\Serializer\Json| \PHPUnit_Framework_MockObject_MockObject
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
        $this->assertEquals('A', $this->packageInfo->getModuleName('a'));
        $this->assertEquals('B', $this->packageInfo->getModuleName('b'));
        $this->assertEquals('C', $this->packageInfo->getModuleName('c'));
        $this->assertEquals('D', $this->packageInfo->getModuleName('d'));
        $this->assertEquals('E', $this->packageInfo->getModuleName('e'));
        $this->assertEquals(
            'Magento_TestModuleName',
            $this->packageInfo->getModuleName('magento/module-test-module-name')
        );
        $this->assertArrayHasKey('Magento_TestModuleName', $this->packageInfo->getNonExistingDependencies());
    }

    public function testGetPackageName()
    {
        $this->assertEquals('a', $this->packageInfo->getPackageName('A'));
        $this->assertEquals('b', $this->packageInfo->getPackageName('B'));
        $this->assertEquals('c', $this->packageInfo->getPackageName('C'));
        $this->assertEquals('d', $this->packageInfo->getPackageName('D'));
        $this->assertEquals('e', $this->packageInfo->getPackageName('E'));
    }

    public function testGetRequireReturnModuleName()
    {
        $this->assertEquals(['B'], $this->packageInfo->getRequire('A'));
        $this->assertEquals(['D'], $this->packageInfo->getRequire('B'));
        $this->assertEquals(['E'], $this->packageInfo->getRequire('C'));
        $this->assertEquals([], $this->packageInfo->getRequire('D'));
        $this->assertEquals([], $this->packageInfo->getRequire('E'));
    }

    public function testGetConflictReturnModuleName()
    {
        $this->assertEquals(['C' => '0.1'], $this->packageInfo->getConflict('A'));
        $this->assertEquals([], $this->packageInfo->getConflict('B'));
        $this->assertEquals([], $this->packageInfo->getConflict('C'));
        $this->assertEquals(['C' => '0.1'], $this->packageInfo->getConflict('D'));
        $this->assertEquals([], $this->packageInfo->getConflict('E'));
    }

    public function testGetVersion()
    {
        $this->assertEquals('0.1', $this->packageInfo->getVersion('A'));
        $this->assertEquals('0.2', $this->packageInfo->getVersion('B'));
        $this->assertEquals('0.1', $this->packageInfo->getVersion('C'));
        $this->assertEquals('0.3', $this->packageInfo->getVersion('D'));
        $this->assertEquals('0.4', $this->packageInfo->getVersion('E'));
        $this->assertEquals('', $this->packageInfo->getVersion('F'));
    }

    public function testGetRequiredBy()
    {
        $this->assertEquals(['A'], $this->packageInfo->getRequiredBy('b'));
    }
}
