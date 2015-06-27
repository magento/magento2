<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests Magento\Framework\ComposerInformation
 */
class ComposerInformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    public function setUp()
    {
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);

    }

    /**
     * Setup DirectoryReadMock to use a specified directory for reading composer files
     *
     * @param $composerDir string Directory under _files that contains composer files
     */
    private function setupDirectoryMock($composerDir)
    {
        $valueMap = [
            [DirectoryList::CONFIG, __DIR__ . '/_files/'],
            [DirectoryList::ROOT, __DIR__ . '/_files/' . $composerDir],
            [DirectoryList::COMPOSER_HOME, __DIR__ . '/_files/' . $composerDir],
        ];

        $this->directoryList->expects($this->any())
            ->method('getPath')
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

        $composerInfo = new ComposerInformation(new MagentoComposerApplicationFactory($this->directoryList));

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
        $composerInfo = new ComposerInformation(new MagentoComposerApplicationFactory($this->directoryList));
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
        $composerInfo = new ComposerInformation(new MagentoComposerApplicationFactory($this->directoryList));

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
        new ComposerInformation(new MagentoComposerApplicationFactory($this->directoryList));
    }
}
