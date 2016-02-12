<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Currency;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Test for Magento\Framework\Currency
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $testCacheDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '_files') . DIRECTORY_SEPARATOR;
        $writer = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface', [], [], '', false, false);
        $writer->expects($this->once())->method('getAbsolutePath')->willReturn($testCacheDir);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false, false);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CACHE)
            ->willReturn($writer);

        // Create new currency object, test values for cache directory and file permission options
        $currency = new Currency($filesystem);
        $this->assertEquals(
            DriverInterface::WRITEABLE_FILE_MODE,
            $currency->getCache()->getBackend()->getOption('cache_file_perm')
        );
        $this->assertEquals($testCacheDir, $currency->getCache()->getBackend()->getOption('cache_dir'));
    }
}
