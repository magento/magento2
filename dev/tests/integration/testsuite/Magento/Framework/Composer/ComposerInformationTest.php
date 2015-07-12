<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

/**
 * Tests Magento\Framework\ComposerInformation
 */
class ComposerInformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    private $directoryReadMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Composer\IO\BufferIO
     */
    private $ioMock;

    /**
     * @var BufferIoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bufferIoFactoryMock;

    public function setUp()
    {
        $this->directoryReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->filesystemMock
            ->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryReadMock));
        $this->ioMock = $this->getMock('Composer\IO\BufferIO', [], [], '', false);
        $this->bufferIoFactoryMock = $this->getMock('Magento\Framework\Composer\BufferIoFactory', [], [], '', false);
        $this->bufferIoFactoryMock->expects($this->any())->method('create')->willReturn($this->ioMock);
    }

    /**
     * Setup DirectoryReadMock to use a specified directory for reading composer files
     *
     * @param $composerDir string Directory under _files that contains composer files
     */
    private function setupDirectoryMock($composerDir)
    {
        $valueMap =                 [
            ['vendor_path.php', null, __DIR__ . '/_files/vendor_path.php'],
            [null, null,  __DIR__ . '/_files/' . $composerDir],
        ];

        $this->directoryReadMock
            ->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValueMap($valueMap));
    }

    /**
     * @param $composerDir string Directory under _files that contains composer files
     *
     * @dataProvider getRequiredPhpVersionDataProvider
     */
    public function testGetRequiredPhpVersion($composerDir)
    {
        $this->setupDirectoryMock($composerDir);
        $composerInfo = new ComposerInformation($this->filesystemMock, $this->bufferIoFactoryMock);
        $this->assertEquals("~5.5.0|~5.6.0", $composerInfo->getRequiredPhpVersion());
    }

    /**
     * @param $composerDir string Directory under _files that contains composer files
     *
     * @dataProvider getRequiredPhpVersionDataProvider
     */
    public function testGetRequiredExtensions($composerDir)
    {
        $this->setupDirectoryMock($composerDir);
        $composerInfo = new ComposerInformation($this->filesystemMock, $this->bufferIoFactoryMock);
        $expectedExtensions = ['ctype', 'gd', 'spl', 'dom', 'simplexml', 'mcrypt', 'hash', 'curl', 'iconv', 'intl'];

        $actualRequiredExtensions = $composerInfo->getRequiredExtensions();
        foreach ($expectedExtensions as $expectedExtension) {
            $this->assertContains($expectedExtension, $actualRequiredExtensions);
        }
    }

    /**
     * @param $composerDir string Directory under _files that contains composer files
     *
     * @dataProvider getRequiredPhpVersionDataProvider
     */
    public function testGetRootRequiredPackagesAndTypes($composerDir)
    {
        $this->setupDirectoryMock($composerDir);
        $composerInfo = new ComposerInformation($this->filesystemMock, $this->bufferIoFactoryMock);

        $requiredPackagesAndTypes = $composerInfo->getRootRequiredPackageTypesByName();

        $this->assertArrayHasKey('composer/composer', $requiredPackagesAndTypes);
        $this->assertEquals('library', $requiredPackagesAndTypes['composer/composer']);
    }

    /**
     * Data provider that returns directories containing different types of composer files.
     *
     * @return array
     */
    public function getRequiredPhpVersionDataProvider()
    {
        return [
            'Skeleton Composer' => ['testSkeleton'],
            'Composer.json from git clone' => ['testFromClone'],
            'Composer.json from git create project' => ['testFromCreateProject'],
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Composer file not found:
     */
    public function testNoLock()
    {
        $this->setupDirectoryMock('notARealDirectory');
        new ComposerInformation($this->filesystemMock, $this->bufferIoFactoryMock);
    }
}
