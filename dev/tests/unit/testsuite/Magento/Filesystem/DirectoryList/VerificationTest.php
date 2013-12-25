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

namespace Magento\Filesystem\DirectoryList;

use Magento\App\State;

class VerificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for createAndVerifyDirectories method
     *
     * @param string $mode
     * @param array $expectedDirs
     * @dataProvider createAndVerifyDirectoriesDataProvider
     */
    public function testCreateAndVerifyDirectories($mode, $expectedDirs)
    {
        $verification = $this->getVerificationInstance($expectedDirs, $mode);
        $verification->createAndVerifyDirectories();
    }

    /**
     * Data provider for testCreateAndVerifyDirectories
     *
     * @return array
     */
    public static function createAndVerifyDirectoriesDataProvider()
    {
        return array(
            'developer mode' => array(
                State::MODE_DEVELOPER,
                array(
                    \Magento\Filesystem::MEDIA => array(true, true, 'base_dir/pub/media'),
                    \Magento\Filesystem::STATIC_VIEW => array(true, true, 'base_dir/pub/static'),
                    \Magento\Filesystem::VAR_DIR => array(true, true, 'base_dir/var'),
                    \Magento\Filesystem::TMP => array(true, true, 'base_dir/var/tmp'),
                    \Magento\Filesystem::CACHE => array(true, true, 'base_dir/var/cache'),
                    \Magento\Filesystem::LOG => array(true, true, 'base_dir/var/log'),
                    \Magento\Filesystem::SESSION => array(true, true, 'base_dir/var/session')
                ),
            ),
            'with_not_existing_dirs' => array(
                State::MODE_DEFAULT,
                array(
                    \Magento\Filesystem::MEDIA => array(false, true, 'base_dir/pub/media'),
                    \Magento\Filesystem::STATIC_VIEW => array(true, true, 'base_dir/pub/static'),
                    \Magento\Filesystem::VAR_DIR => array(false, true, 'base_dir/var'),
                    \Magento\Filesystem::TMP => array(true, true, 'base_dir/var/tmp'),
                    \Magento\Filesystem::CACHE => array(false, true, 'base_dir/var/cache'),
                    \Magento\Filesystem::LOG => array(true, true, 'base_dir/var/log'),
                    \Magento\Filesystem::SESSION => array(false, true, 'base_dir/var/session')
                ),
            ),
            'production mode' => array(
                State::MODE_PRODUCTION,
                array(
                    \Magento\Filesystem::MEDIA => array(true, true, 'base_dir/pub/media'),
                    \Magento\Filesystem::VAR_DIR => array(true, true, 'base_dir/var'),
                    \Magento\Filesystem::TMP => array(true, true, 'base_dir/var/tmp'),
                    \Magento\Filesystem::CACHE => array(true, true, 'base_dir/var/cache'),
                    \Magento\Filesystem::LOG => array(true, true, 'base_dir/var/log'),
                    \Magento\Filesystem::SESSION => array(true, true, 'base_dir/var/session')
                ),
            ),
        );
    }

    /**
     * Test for createAndVerifyDirectories method if some directories are not writable
     *
     * @param string $mode
     * @param array $expectedDirs
     * @dataProvider notWritableDataProvider
     * @expectedException \Magento\BootstrapException
     */
    public function testNotWritable($mode, $expectedDirs)
    {
        $verification = $this->getVerificationInstance($expectedDirs, $mode);
        $verification->createAndVerifyDirectories();
    }

    /**
     * Data provider for testNotWritable
     *
     * @return array
     */
    public static function notWritableDataProvider()
    {
        return array(
            'developer mode' => array(
                State::MODE_DEVELOPER,
                array(
                    \Magento\Filesystem::MEDIA => array(true, false, 'base_dir/pub/media'),
                    \Magento\Filesystem::STATIC_VIEW => array(true, true, 'base_dir/pub/static'),
                    \Magento\Filesystem::VAR_DIR => array(true, false, 'base_dir/var'),
                    \Magento\Filesystem::TMP => array(true, true, 'base_dir/var/tmp'),
                    \Magento\Filesystem::CACHE => array(true, false, 'base_dir/var/cache'),
                    \Magento\Filesystem::LOG => array(true, true, 'base_dir/var/log'),
                    \Magento\Filesystem::SESSION => array(true, false, 'base_dir/var/session')
                ),
            )
        );
    }

    /**
     * Test for createAndVerifyDirectories method if some directories cannot be created
     *
     * @param string $mode
     * @param array $expectedDirs
     * @dataProvider createExceptionDataProvider
     * @expectedException \Magento\BootstrapException
     */
    public function testCreateException($mode, $expectedDirs)
    {
        $verification = $this->getVerificationInstance($expectedDirs, $mode);
        $verification->createAndVerifyDirectories();
    }

    /**
     * Data provider for testCreateException
     *
     * @return array
     */
    public static function createExceptionDataProvider()
    {
        return array(
            'developer mode' => array(
                State::MODE_DEVELOPER,
                array(
                    \Magento\Filesystem::MEDIA => array(true, false, 'base_dir/pub/media', true),
                    \Magento\Filesystem::STATIC_VIEW => array(true, true, 'base_dir/pub/static'),
                    \Magento\Filesystem::VAR_DIR => array(true, false, 'base_dir/var'),
                    \Magento\Filesystem::TMP => array(true, true, 'base_dir/var/tmp', true),
                    \Magento\Filesystem::CACHE => array(true, false, 'base_dir/var/cache'),
                    \Magento\Filesystem::LOG => array(true, true, 'base_dir/var/log'),
                    \Magento\Filesystem::SESSION => array(true, false, 'base_dir/var/session', true)
                ),
            )
        );
    }

    /**
     * Get verification instance
     *
     * @param array $expectedDirs
     * @param string $mode
     * @return Verification
     */
    protected function getVerificationInstance(array $expectedDirs, $mode)
    {
        $filesystem = $this->getFilesystemMock($expectedDirs);
        $appState = $this->getMock('Magento\App\State', array('getMode'), array(), '', false);
        $appState->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($mode));
        return new Verification($filesystem, $appState);
    }

    /**
     * Get filesystem mock
     *
     * @param array $dirsToVerify
     * @return \Magento\Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilesystemMock(array $dirsToVerify)
    {
        $filesystem = $this->getMock('Magento\Filesystem', array('getDirectoryWrite', '__wakeup'), array(), '', false);
        $valueMap = array();
        foreach ($dirsToVerify as $code => $config) {
            $createException = isset($config[3]) ? $config[3] : false;
            $directory = $this->getDirectoryMock($config[0], $config[1], $config[2], $createException);
            $valueMap[] = array($code, $directory);
        }
        $filesystem->expects($this->exactly(count($dirsToVerify)))
            ->method('getDirectoryWrite')
            ->will($this->returnValueMap($valueMap));

        return $filesystem;
    }

    /**
     * Get directory mock
     *
     * @param bool $existing
     * @param bool $writable
     * @param string $absolutePath
     * @param bool $createException
     * @return \Magento\Filesystem\Directory\Write | \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDirectoryMock($existing, $writable, $absolutePath, $createException)
    {
        $directory = $this->getMock(
            'Magento\Filesystem\Directory\Write',
            array('isExist', 'isWritable', 'getAbsolutePath', 'create'),
            array(),
            '',
            false
        );
        $directory->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue($existing));

        if (!$existing) {
            if (!$createException) {
                $directory->expects($this->once())
                    ->method('create');
            } else {
                $directory->expects($this->once())
                    ->method('create')
                    ->will($this->throwException(new \Magento\Filesystem\FilesystemException('')));
            }
            return $directory;
        }

        $directory->expects($this->once())
            ->method('isWritable')
            ->will($this->returnValue($writable));

        if (!$writable) {
            $directory->expects($this->once())
                ->method('getAbsolutePath')
                ->will($this->returnValue($absolutePath));
        }

        return $directory;
    }
}
