<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Helper;

use Magento\Backup\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnCallback(function ($code) {
                $dir = $this->getMockForAbstractClass(ReadInterface::class);
                $dir->expects($this->any())
                    ->method('getAbsolutePath')
                    ->willReturnCallback(function ($path) use ($code) {
                        $path = empty($path) ? $path : '/' . $path;
                        return rtrim($code, '/') . $path;
                    });
                return $dir;
            });

        $this->helper = (new ObjectManager($this))
            ->getObject(
                Data::class,
                ['filesystem' => $this->filesystem]
            );
    }

    public function testGetBackupIgnorePaths()
    {
        $this->assertEquals(
            [
                '.git',
                '.svn',
                MaintenanceMode::FLAG_DIR . '/' . MaintenanceMode::FLAG_FILENAME,
                DirectoryList::SESSION,
                DirectoryList::CACHE,
                DirectoryList::LOG,
                DirectoryList::VAR_DIR . '/full_page_cache',
                DirectoryList::VAR_DIR . '/locks',
                DirectoryList::VAR_DIR . '/report',
            ],
            $this->helper->getBackupIgnorePaths()
        );
    }

    public function testGetRollbackIgnorePaths()
    {
        $this->assertEquals(
            [
                '.svn',
                '.git',
                'var/' . MaintenanceMode::FLAG_FILENAME,
                DirectoryList::SESSION,
                DirectoryList::LOG,
                DirectoryList::VAR_DIR . '/locks',
                DirectoryList::VAR_DIR . '/report',
                DirectoryList::ROOT . '/errors',
                DirectoryList::ROOT . '/index.php',
            ],
            $this->helper->getRollbackIgnorePaths()
        );
    }
}
