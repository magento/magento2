<?php
/**
 * Test for \Magento\Framework\Filesystem\Io\File
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Io;

use Magento\Framework\Exception\FileSystemException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify filesystem client
 */
class FileTest extends TestCase
{

    /**
     * @var File
     */
    private $io;

    /**
     * @var String
     */
    private $absolutePath;

    /**
     * @var String
     */
    private $generatedPath;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->io = new File();
        $this->absolutePath = Bootstrap::getInstance()->getAppTempDir();
        $this->generatedPath = $this->getTestPath('/rollback_test_');
        $this->io->mkdir($this->generatedPath);
    }

    /**
     * @inheritdoc
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->removeGeneratedDirectory();
    }

    /**
     * Verify file put without content.
     *
     * @return void
     */
    public function testWrite(): void
    {
        $path = $this->generatedPath . '/file_three.txt';
        $this->assertEquals(0, $this->io->write($path, '', 0444));
        $this->assertFalse(is_writable($path));
    }

    /**
     * Returns relative path for the test.
     *
     * @param $relativePath
     * @return string
     */
    protected function getTestPath($relativePath): string
    {
        return $this->absolutePath . $relativePath . time();
    }

    /**
     * Remove generated directories.
     *
     * @return void
     */
    private function removeGeneratedDirectory(): void
    {
        if (is_dir($this->generatedPath)) {
            $this->io->rmdir($this->generatedPath, true);
        }
    }
}
