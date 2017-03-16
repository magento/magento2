<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\View\Deployment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\App\View\Deployment\Version\Storage\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var File
     */
    private $fileStorage;

    /**
     * @var WriteInterface
     */
    private $directoryWrite;

    /**
     * @var string
     */
    private $fileName = 'deployed_version.txt';

    public function setUp()
    {
        $this->fileStorage = ObjectManager::getInstance()->create(
            File::class,
            [
                'directoryCode' => DirectoryList::STATIC_VIEW,
                'fileName' => $this->fileName
            ]
        );
        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = ObjectManager::getInstance()->get(\Magento\TestFramework\App\Filesystem::class);
        $this->directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->removeDeployVersionFile();
    }

    /**
     * @param string $mode
     * @return Version
     */
    public function getVersionModel($mode)
    {
        $appState = ObjectManager::getInstance()->create(
            State::class,
            [
                'mode' => $mode
            ]
        );
        return ObjectManager::getInstance()->create(
            Version::class,
            [
                'appState' => $appState
            ]
        );
    }

    protected function tearDown()
    {
        $this->removeDeployVersionFile();
    }

    private function removeDeployVersionFile()
    {
        if ($this->directoryWrite->isExist($this->fileName)) {
            $this->directoryWrite->delete($this->fileName);
        }
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetValueInProductionModeWithoutVersion()
    {
        $this->assertFalse($this->directoryWrite->isExist($this->fileName));
        $this->getVersionModel(State::MODE_PRODUCTION)->getValue();
    }

    public function testGetValueInDeveloperMode()
    {
        $this->assertFalse($this->directoryWrite->isExist($this->fileName));
        $this->getVersionModel(State::MODE_DEVELOPER)->getValue();
        $this->assertTrue($this->directoryWrite->isExist($this->fileName));
    }

    /**
     * Assert that version is not regenerated on each request in developer mode
     */
    public function testGetValue()
    {
        $this->assertFalse($this->directoryWrite->isExist($this->fileName));
        $versionModel = $this->getVersionModel(State::MODE_DEVELOPER);
        $version = $versionModel->getValue();
        $this->assertTrue($this->directoryWrite->isExist($this->fileName));
        $this->assertEquals($version, $versionModel->getValue());
    }
}
