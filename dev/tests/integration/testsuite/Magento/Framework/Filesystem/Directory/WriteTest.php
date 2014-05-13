<?php
/**
 * Test for \Magento\Framework\Filesystem\Directory\Write
 *
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
namespace Magento\Framework\Filesystem\Directory;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ReadTest
 * Test for Magento\Framework\Filesystem\Directory\Read class
 */
class WriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test data to be cleaned
     *
     * @var array
     */
    private $testDirectories = array();

    /**
     * Test instance of Read
     */
    public function testInstance()
    {
        $dir = $this->getDirectoryInstance('newDir1', 0777);
        $this->assertTrue($dir instanceof ReadInterface);
        $this->assertTrue($dir instanceof WriteInterface);
    }

    /**
     * Test for create method
     *
     * @dataProvider createProvider
     * @param string $basePath
     * @param int $permissions
     * @param string $path
     */
    public function testCreate($basePath, $permissions, $path)
    {
        $directory = $this->getDirectoryInstance($basePath, $permissions);
        $this->assertTrue($directory->create($path));
        $this->assertTrue($directory->isExist($path));
    }

    /**
     * Data provider for testCreate
     *
     * @return array
     */
    public function createProvider()
    {
        return array(
            array('newDir1', 0777, "newDir1"),
            array('newDir1', 0777, "root_dir1/subdir1/subdir2"),
            array('newDir2', 0755, "root_dir2/subdir"),
            array('newDir1', 0777, ".")
        );
    }

    /**
     * Test for delete method
     *
     * @dataProvider deleteProvider
     * @param string $path
     */
    public function testDelete($path)
    {
        $directory = $this->getDirectoryInstance('newDir', 0777);
        $directory->create($path);
        $this->assertTrue($directory->isExist($path));
        $directory->delete($path);
        $this->assertFalse($directory->isExist($path));
    }

    /**
     * Data provider for testDelete
     *
     * @return array
     */
    public function deleteProvider()
    {
        return array(array('subdir'), array('subdir/subsubdir'));
    }

    /**
     * Test for rename method (in scope of one directory instance)
     *
     * @dataProvider renameProvider
     * @param string $basePath
     * @param int $permissions
     * @param string $name
     * @param string $newName
     */
    public function testRename($basePath, $permissions, $name, $newName)
    {
        $directory = $this->getDirectoryInstance($basePath, $permissions);
        $directory->touch($name);
        $created = $directory->read();
        $directory->renameFile($name, $newName);
        $renamed = $directory->read();
        $this->assertTrue(in_array($name, $created));
        $this->assertTrue(in_array($newName, $renamed));
        $this->assertFalse(in_array($name, $renamed));
    }

    /**
     * Data provider for testRename
     *
     * @return array
     */
    public function renameProvider()
    {
        return array(array('newDir1', 0777, 'first_name.txt', 'second_name.txt'));
    }

    /**
     * Test for rename method (moving to new directory instance)
     *
     * @dataProvider renameTargetDirProvider
     * @param string $firstDir
     * @param string $secondDir
     * @param int $permission
     * @param string $name
     * @param string $newName
     */
    public function testRenameTargetDir($firstDir, $secondDir, $permission, $name, $newName)
    {
        $dir1 = $this->getDirectoryInstance($firstDir, $permission);
        $dir2 = $this->getDirectoryInstance($secondDir, $permission);

        $dir1->touch($name);
        $created = $dir1->read();
        $dir1->renameFile($name, $newName, $dir2);
        $oldPlace = $dir1->read();

        $this->assertTrue(in_array($name, $created));
        $this->assertFalse(in_array($name, $oldPlace));
    }

    /**
     * Data provider for testRenameTargetDir
     *
     * @return array
     */
    public function renameTargetDirProvider()
    {
        return array(array('dir1', 'dir2', 0777, 'first_name.txt', 'second_name.txt'));
    }

    /**
     * Test for copy method (copy in scope of one directory instance)
     *
     * @dataProvider renameProvider
     * @param string $basePath
     * @param int $permissions
     * @param string $name
     * @param string $newName
     */
    public function testCopy($basePath, $permissions, $name, $newName)
    {
        $directory = $this->getDirectoryInstance($basePath, $permissions);
        $file = $directory->openFile($name, 'w+');
        $file->close();
        $directory->copyFile($name, $newName);
        $this->assertTrue($directory->isExist($name));
        $this->assertTrue($directory->isExist($newName));
    }

    /**
     * Data provider for testCopy
     *
     * @return array
     */
    public function copyProvider()
    {
        return array(
            array('newDir1', 0777, 'first_name.txt', 'second_name.txt'),
            array('newDir1', 0777, 'subdir/first_name.txt', 'subdir/second_name.txt')
        );
    }

    /**
     * Test for copy method (copy to another directory instance)
     *
     * @dataProvider copyTargetDirProvider
     * @param string $firstDir
     * @param string $secondDir
     * @param int $permission
     * @param string $name
     * @param string $newName
     */
    public function testCopyTargetDir($firstDir, $secondDir, $permission, $name, $newName)
    {
        $dir1 = $this->getDirectoryInstance($firstDir, $permission);
        $dir2 = $this->getDirectoryInstance($secondDir, $permission);

        $file = $dir1->openFile($name, 'w+');
        $file->close();
        $dir1->copyFile($name, $newName, $dir2);

        $this->assertTrue($dir1->isExist($name));
        $this->assertTrue($dir2->isExist($newName));
    }

    /**
     * Data provider for testCopyTargetDir
     *
     * @return array
     */
    public function copyTargetDirProvider()
    {
        return array(
            array('dir1', 'dir2', 0777, 'first_name.txt', 'second_name.txt'),
            array('dir1', 'dir2', 0777, 'subdir/first_name.txt', 'subdir/second_name.txt')
        );
    }

    /**
     * Test for changePermissions method
     */
    public function testChangePermissions()
    {
        $directory = $this->getDirectoryInstance('newDir1', 0777);
        $directory->create('test_directory');
        $this->assertTrue($directory->changePermissions('test_directory', 0644));
    }

    /**
     * Test for touch method
     *
     * @dataProvider touchProvider
     * @param string $basePath
     * @param int $permissions
     * @param string $path
     * @param int $time
     */
    public function testTouch($basePath, $permissions, $path, $time)
    {
        $directory = $this->getDirectoryInstance($basePath, $permissions);
        $directory->openFile($path);
        $this->assertTrue($directory->touch($path, $time));
        $this->assertEquals($time, $directory->stat($path)['mtime']);
    }

    /**
     * Data provider for testTouch
     *
     * @return array
     */
    public function touchProvider()
    {
        return array(
            array('test_directory', 0777, 'touch_file.txt', time() - 3600),
            array('test_directory', 0777, 'subdirectory/touch_file.txt', time() - 3600)
        );
    }

    /**
     * Test isWritable method
     */
    public function testIsWritable()
    {
        $directory = $this->getDirectoryInstance('newDir1', 0777);
        $directory->create('bar');
        $this->assertFalse($directory->isWritable('not_existing_dir'));
        $this->assertTrue($directory->isWritable('bar'));
    }

    /**
     * Test for openFile method
     *
     * @dataProvider openFileProvider
     * @param string $basePath
     * @param int $permissions
     * @param string $path
     * @param string $mode
     */
    public function testOpenFile($basePath, $permissions, $path, $mode)
    {
        $directory = $this->getDirectoryInstance($basePath, $permissions);
        $file = $directory->openFile($path, $mode);
        $this->assertTrue($file instanceof \Magento\Framework\Filesystem\File\WriteInterface);
        $file->close();
    }

    /**
     * Data provider for testOpenFile
     *
     * @return array
     */
    public function openFileProvider()
    {
        return array(
            array('newDir1', 0777, 'newFile.txt', 'w+'),
            array('newDir1', 0777, 'subdirectory/newFile.txt', 'w+')
        );
    }

    /**
     * Test writeFile
     *
     * @dataProvider writeFileProvider
     * @param string $path
     * @param string $content
     * @param string $extraContent
     */
    public function testWriteFile($path, $content, $extraContent)
    {
        $directory = $this->getDirectoryInstance('writeFileDir', 0777);
        $directory->writeFile($path, $content);
        $this->assertEquals($content, $directory->readFile($path));
        $directory->writeFile($path, $extraContent);
        $this->assertEquals($extraContent, $directory->readFile($path));
    }

    /**
     * Test writeFile for append mode
     *
     * @dataProvider writeFileProvider
     * @param string $path
     * @param string $content
     * @param string $extraContent
     */
    public function testWriteFileAppend($path, $content, $extraContent)
    {
        $directory = $this->getDirectoryInstance('writeFileDir', 0777);
        $directory->writeFile($path, $content, 'a+');
        $this->assertEquals($content, $directory->readFile($path));
        $directory->writeFile($path, $extraContent, 'a+');
        $this->assertEquals($content . $extraContent, $directory->readFile($path));
    }

    /**
     * Data provider for testWriteFile and testWriteFileAppend
     *
     * @return array
     */
    public function writeFileProvider()
    {
        return array(array('file1', '123', '456'), array('folder1/file1', '123', '456'));
    }

    /**
     * Tear down
     */
    public function tearDown()
    {
        /** @var Write $directory */
        foreach ($this->testDirectories as $directory) {
            if ($directory->isExist()) {
                $directory->delete();
            }
        }
    }

    /**
     * Get readable file instance
     * Get full path for files located in _files directory
     *
     * @param string $path
     * @param string $permissions
     * @return Write
     */
    private function getDirectoryInstance($path, $permissions)
    {
        $fullPath = __DIR__ . '/../_files/' . $path;
        $config = array('path' => $fullPath, 'permissions' => $permissions, 'allow_create_dirs' => true);
        $objectManager = Bootstrap::getObjectManager();
        $directoryFactory = $objectManager->create('Magento\Framework\Filesystem\Directory\WriteFactory');
        $directory = $directoryFactory->create(
            $config,
            new \Magento\Framework\Filesystem\DriverFactory(
                $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList')
            )
        );
        $this->testDirectories[] = $directory;
        return $directory;
    }
}
