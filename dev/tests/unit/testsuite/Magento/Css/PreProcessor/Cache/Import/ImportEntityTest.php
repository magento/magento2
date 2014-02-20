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

namespace Magento\Css\PreProcessor\Cache\Import;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ImportEntityTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Css\PreProcessor\Cache\Import\ImportEntity */
    protected $importEntity;

    /**
     * @var \Magento\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileSystemMock;

    /**
     * @var string
     */
    protected $absolutePath;

    /**
     * @param string $relativePath
     * @param int $originalMtime
     */
    protected function createMock($relativePath, $originalMtime)
    {
        $filePath = 'someFile';
        $params = ['some', 'params'];
        $this->absoluteFilePath = 'some_absolute_path';

        $this->rootDirectory = $this->getMock('Magento\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->rootDirectory->expects($this->once())
            ->method('getRelativePath')
            ->with($this->equalTo($this->absoluteFilePath))
            ->will($this->returnValue($relativePath));

        $this->rootDirectory->expects($this->atLeastOnce())
            ->method('stat')
            ->with($this->equalTo($relativePath))
            ->will($this->returnValue(['mtime' => $originalMtime]));

        $this->filesystemMock = $this->getMock('Magento\Filesystem', [], [], '', false);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with($this->equalTo(\Magento\App\Filesystem::ROOT_DIR))
            ->will($this->returnValue($this->rootDirectory));

        $this->fileSystemMock = $this->getMock('Magento\View\FileSystem', [], [], '', false);
        $this->fileSystemMock->expects($this->once())
            ->method('getViewFile')
            ->with($this->equalTo($filePath), $this->equalTo($params))
            ->will($this->returnValue($this->absoluteFilePath));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        /** @var \Magento\Css\PreProcessor\Cache\Import\ImportEntity importEntity */
        $this->importEntity = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\Import\ImportEntity',
            [
                'filesystem' => $this->filesystemMock,
                'viewFileSystem' => $this->fileSystemMock,
                'filePath' => $filePath,
                'params' => $params
            ]
        );
        $rootDirectoryProperty = new \ReflectionProperty($this->importEntity, 'rootDirectory');
        $rootDirectoryProperty->setAccessible(true);
        $this->assertEquals($this->rootDirectory, $rootDirectoryProperty->getValue($this->importEntity));
    }

    public function testGetOriginalFile()
    {
        $mtime = rand();
        $relativePath = '/some/relative/path/to/file.less';
        $this->createMock($relativePath, $mtime);
        $this->assertEquals($relativePath, $this->importEntity->getOriginalFile());
    }

    public function testGetOriginalMtime()
    {
        $mtime = rand();
        $relativePath = '/some/relative/path/to/file2.less';
        $this->createMock($relativePath, $mtime);
        $this->assertEquals($mtime, $this->importEntity->getOriginalMtime());
    }

    /**
     * @param bool $isFile
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($isFile)
    {
        $mtime = rand();
        $relativePath = '/some/relative/path/to/file3.less';
        $this->createMock($relativePath, $mtime);
        $this->rootDirectory->expects($this->once())
            ->method('isFile')
            ->with($this->equalTo($relativePath))
            ->will($this->returnValue($isFile));
        $this->assertEquals($isFile, $this->importEntity->isValid());
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function test__sleep()
    {
        $mtime = rand();
        $relativePath = '/some/relative/path/to/file3.less';
        $this->createMock($relativePath, $mtime);
        $this->assertEquals(['originalFile', 'originalMtime'], $this->importEntity->__sleep());
    }
}
