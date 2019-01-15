<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

/**
 * A test that enforces composer.lock is up to date with composer.json
 */
class ComposerLockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return string
     */
    public function testLockFileExists()
    {
        $lockFilePath = BP . '/composer.lock';
        $this->assertLockFileExists($lockFilePath);
        return $lockFilePath;
    }

    /**
     * @depends testLockFileExists
     * @param string $lockFilePath
     * @return string
     */
    public function testLockFileReadable($lockFilePath)
    {
        $this->assertLockFileReadable($lockFilePath);
        return $lockFilePath;
    }

    /**
     * @depends testLockFileReadable
     * @param string $lockFilePath
     * @return string
     */
    public function testLockFileContainsJson($lockFilePath)
    {
        $lockFileContent = file_get_contents($lockFilePath);
        $this->assertLockFileContainsValidJson($lockFileContent);
        return $lockFileContent;
    }

    /**
     * @depends testLockFileContainsJson
     * @param string $lockFileContent
     */
    public function testUpToDate($lockFileContent)
    {
        $lockData = json_decode($lockFileContent, true);
        $composerFilePath = BP . '/composer.json';
        $this->assertLockDataRelevantToComposerFile($lockData, $composerFilePath);
    }

    /**
     * @param string $lockFilePath
     */
    private function assertLockFileExists($lockFilePath)
    {
        $this->assertFileExists($lockFilePath, 'composer.lock file does not exist');
    }

    /**
     * @param string $lockFilePath
     */
    private function assertLockFileReadable($lockFilePath)
    {
        if (!is_readable($lockFilePath)) {
            $this->fail('composer.lock file is not readable');
        }
    }

    /**
     * @param string $lockFileContent
     */
    private function assertLockFileContainsValidJson($lockFileContent)
    {
        $this->assertJson($lockFileContent, 'composer.lock file does not contains valid json');
    }

    /**
     * @param array $lockData
     * @param string $composerFilePath
     */
    private function assertLockDataRelevantToComposerFile(array $lockData, $composerFilePath)
    {
        if (isset($lockData['content-hash'])) {
            $this->assertLockDataRelevantToMeaningfulComposerConfig($lockData, $composerFilePath);
        } else if (isset($lockData['hash'])) {
            $this->assertLockDataRelevantToFullComposerConfig($lockData, $composerFilePath);
        } else {
            $this->fail('composer.lock does not linked to composer.json data');
        }
    }

    /**
     * @param array $lockData
     * @param string $composerFilePath
     */
    private function assertLockDataRelevantToMeaningfulComposerConfig(array $lockData, $composerFilePath)
    {
        $contentHashCalculator = 'Composer\Package\Locker::getContentHash';
        if (!is_callable($contentHashCalculator)) {
            $this->markTestSkipped('Unable to check composer.lock file by content hash');
        }

        $composerContentHash = call_user_func($contentHashCalculator, file_get_contents($composerFilePath));
        $this->assertSame(
            $composerContentHash,
            $lockData['content-hash'],
            'composer.lock file is not up to date (composer.json file was modified)'
        );
    }

    /**
     * @param array $lockData
     * @param string $composerFilePath
     */
    private function assertLockDataRelevantToFullComposerConfig(array $lockData, $composerFilePath)
    {
        $composerFileHash = hash_file('md5', $composerFilePath);
        $this->assertSame(
            $composerFileHash,
            $lockData['hash'],
            'composer.lock file is not up to date (composer.json file was modified)'
        );
    }
}
