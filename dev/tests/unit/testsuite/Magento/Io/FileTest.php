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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * \Magento\Io\File test case
 */
namespace Magento\Io;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string $_dir
     */
    protected $_dir;

    /**
     * @var string $_file
     */
    protected $_file;

    protected function setUp()
    {
        try {
            $this->_dir = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'directory';
            $this->_file = $this->_dir . DIRECTORY_SEPARATOR . 'file.txt';
            mkdir($this->_dir, 0700, true);
            if (touch($this->_file)) {
                chmod($this->_file, 0700);
            }
        } catch (\PHPUnit_Framework_Error_Warning $exception) {
            $this->markTestSkipped("Problem with prepare test: " . $exception->getMessage());
        }
    }

    protected function tearDown()
    {
        if (@file_exists($this->_file)) {
            @unlink($this->_file);
        }
        if (@file_exists($this->_dir)) {
            @rmdir($this->_dir);
        }
    }

    public function testChmodRecursive()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped("chmod may not work for Windows");
        }

        $permsBefore = 0700;
        $expected = 0777;
        $this->assertEquals($permsBefore, fileperms($this->_dir) & $permsBefore,
            "Wrong permissions set for " . $this->_dir);
        $this->assertEquals($permsBefore, fileperms($this->_file) & $permsBefore,
            "Wrong permissions set for " . $this->_file);
        \Magento\Io\File::chmodRecursive($this->_dir, $expected);
        $this->assertEquals($expected, fileperms($this->_dir) & $expected,
            "Directory permissions were changed incorrectly.");
        $this->assertEquals($expected, fileperms($this->_file) & $expected,
            "File permissions were changed incorrectly.");

    }

    public function testRmdirRecursive()
    {
        $this->assertFileExists($this->_file);
        \Magento\Io\File::rmdirRecursive($this->_dir);
        $this->assertFileNotExists($this->_dir);
    }
}
