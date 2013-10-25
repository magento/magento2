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

namespace Magento\App\Dir;

class VerificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $mode
     * @param array $expectedDirs
     * @dataProvider createAndVerifyDirectoriesDataProvider
     */
    public function testCreateAndVerifyDirectoriesNonExisting($mode, $expectedDirs)
    {
        $model = $this->_createModelForVerification($mode, false, $actualCreatedDirs, $actualVerifiedDirs);
        $model->createAndVerifyDirectories();

        // Check
        $this->assertEquals($expectedDirs, $actualCreatedDirs);
        $this->assertEmpty($actualVerifiedDirs,
            'Non-existing directories must be just created, no write access verification is needed');
    }

    /**
     * @param string $mode
     * @param array $expectedDirs
     * @dataProvider createAndVerifyDirectoriesDataProvider
     */
    public function testCreateAndVerifyDirectoriesExisting($mode, $expectedDirs)
    {
        $model = $this->_createModelForVerification($mode, true, $actualCreatedDirs, $actualVerifiedDirs);
        $model->createAndVerifyDirectories();

        // Check
        $this->assertEmpty($actualCreatedDirs, 'Directories must not be created, when they exist');
        $this->assertEquals($expectedDirs, $actualVerifiedDirs);
    }

    /**
     * Create model to test creation of directories and verification of their write-access
     *
     * @param string $mode
     * @param bool $isExist
     * @param array $actualCreatedDirs
     * @param array $actualVerifiedDirs
     * @return \Magento\App\Dir\Verification
     */
    protected function _createModelForVerification($mode, $isExist, &$actualCreatedDirs, &$actualVerifiedDirs)
    {
        $dirs = new \Magento\App\Dir('base_dir');
        $appState = new \Magento\App\State(time(), $mode);

        $filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue($isExist));

        $actualCreatedDirs = array();
        $callbackCreate = function ($dir) use (&$actualCreatedDirs) {
            $actualCreatedDirs[] = $dir;
        };
        $filesystem->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnCallback($callbackCreate));

        $actualVerifiedDirs = array();
        $callbackVerify = function ($dir) use (&$actualVerifiedDirs) {
            $actualVerifiedDirs[] = $dir;
            return true;
        };
        $filesystem->expects($this->any())
            ->method('isWritable')
            ->will($this->returnCallback($callbackVerify));

        return new \Magento\App\Dir\Verification(
            $filesystem,
            $dirs,
            $appState
        );
    }

    /**
     * @return array
     */
    public static function createAndVerifyDirectoriesDataProvider()
    {
        return array(
            'developer mode' => array(
                \Magento\App\State::MODE_DEVELOPER,
                array(
                    'base_dir/pub/media',
                    'base_dir/pub/static',
                    'base_dir/var',
                    'base_dir/var/tmp',
                    'base_dir/var/cache',
                    'base_dir/var/log',
                    'base_dir/var/session'
                ),
            ),
            'default mode' => array(
                \Magento\App\State::MODE_DEFAULT,
                array(
                    'base_dir/pub/media',
                    'base_dir/pub/static',
                    'base_dir/var',
                    'base_dir/var/tmp',
                    'base_dir/var/cache',
                    'base_dir/var/log',
                    'base_dir/var/session'
                ),
            ),
            'production mode' => array(
                \Magento\App\State::MODE_PRODUCTION,
                array(
                    'base_dir/pub/media',
                    'base_dir/var',
                    'base_dir/var/tmp',
                    'base_dir/var/cache',
                    'base_dir/var/log',
                    'base_dir/var/session'
                ),
            ),
        );
    }

    public function testCreateAndVerifyDirectoriesCreateException()
    {
        // Plan
        $this->setExpectedException('Magento\BootstrapException',
            'Cannot create or verify write access: base_dir/var/log, base_dir/var/session');

        $dirs = new \Magento\App\Dir('base_dir');
        $appState = new \Magento\App\State(time());

        $callback = function ($dir) {
            if (($dir == 'base_dir/var/log') || ($dir == 'base_dir/var/session')) {
                throw new \Magento\Filesystem\FilesystemException();
            }
        };
        $filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnCallback($callback));

        // Do
        $model = new \Magento\App\Dir\Verification(
            $filesystem,
            $dirs,
            $appState
        );
        $model->createAndVerifyDirectories();
    }

    public function testCreateAndVerifyDirectoriesWritableException()
    {
        // Plan
        $this->setExpectedException('Magento\BootstrapException',
            'Cannot create or verify write access: base_dir/var/log, base_dir/var/session');

        $dirs = new \Magento\App\Dir('base_dir');
        $appState = new \Magento\App\State(time());

        $filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));

        $dirWritableMap = array(
            array('base_dir/pub/media',     null, true),
            array('base_dir/pub/static',    null, true),
            array('base_dir/var',           null, true),
            array('base_dir/var/tmp',       null, true),
            array('base_dir/var/cache',     null, true),
            array('base_dir/var/log',       null, false),
            array('base_dir/var/session',   null, false),
        );
        $filesystem->expects($this->any())
            ->method('isWritable')
            ->will($this->returnValueMap($dirWritableMap));

        // Do
        $model = new \Magento\App\Dir\Verification(
            $filesystem,
            $dirs,
            $appState
        );
        $model->createAndVerifyDirectories();
    }
}
