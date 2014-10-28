<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backup\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    public function setUp()
    {
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnCallback(function ($code) {
                $dir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
                $dir->expects($this->any())
                    ->method('getAbsolutePath')
                    ->will($this->returnCallback(function ($path) use ($code) {
                        $path = empty($path) ? $path : '/' . $path;
                        return rtrim($code, '/') . $path;
                    }));
                return $dir;
            }));

        $this->helper = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Backup\Helper\Data', [
                'filesystem' => $this->filesystem,
            ]);
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
