<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Io;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    private function getTmpDir()
    {
        $tmpDir = '/tmp/magento-' . \microtime(true);
        if (!\file_exists($tmpDir)) {
            \mkdir($tmpDir, 0777, true);
        }
        return $tmpDir;
    }

    /**
     * To cover the issue on GitHub: #27866
     * @throws LocalizedException
     */
    public function testReadShouldCopyTheSourceFileToTheGivenFileResource()
    {
        $content = \random_int(0, 1000);
        $sourceFileName = "source-file.txt";
        $tmpDir = $this->getTmpDir();
        \file_put_contents("{$tmpDir}/{$sourceFileName}", $content);

        $file = new File();
        $targetFileName = "target-file.txt";
        $targetFileHandle = \fopen("{$tmpDir}/{$targetFileName}", 'w');
        $file->cd($tmpDir);
        $file->read($sourceFileName, $targetFileHandle);

        $targetContent = file_get_contents("{$tmpDir}/{$targetFileName}");
        $this->assertEquals($content, $targetContent);
    }
}
