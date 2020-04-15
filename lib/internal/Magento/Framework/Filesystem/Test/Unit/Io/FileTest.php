<?php
namespace Magento\Framework\Filesystem\Test\Unit\Io;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    protected function setUp()
    {

    }

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
     * @test
     * @throws LocalizedException
     */
    public function read_should_copy_the_source_file_to_the_given_file_resource()
    {
        $content = \mt_rand();
        $sourceFileName = "source-file.txt";
        $tmpDir = $this->getTmpDir();
        \file_put_contents("{$tmpDir}/{$sourceFileName}", $content);

        $file = new File();
        $targetFileName = "target-file.txt";
        $targetFileHandle = \fopen("{$tmpDir}/{$targetFileName}" , 'w');
        $file->cd($tmpDir);
        $result = $file->read($sourceFileName, $targetFileHandle);

        $targetContent = file_get_contents("{$tmpDir}/{$targetFileName}");
        $this->assertEquals($content, $targetContent);
    }
}
