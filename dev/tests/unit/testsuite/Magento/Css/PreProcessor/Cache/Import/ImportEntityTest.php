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
     * @var string
     */
    protected $absoluteFilePath;

    /**
     * @param string $relativePath
     * @param int $originalMtime
     */
    protected function createMock($relativePath, $originalMtime)
    {
        $filePath = 'someFile';
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

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $lessFile = $this->getMock('Magento\Less\PreProcessor\File\Less', [], [], '', false);
        $lessFile->expects($this->any())->method('getFilePath')->will($this->returnValue($filePath));
        $lessFile->expects($this->any())->method('getSourcePath')->will($this->returnValue($this->absoluteFilePath));
        $lessFile->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($this->rootDirectory));

        /** @var \Magento\Css\PreProcessor\Cache\Import\ImportEntity importEntity */
        $this->importEntity = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\Import\ImportEntity', ['lessFile' => $lessFile]
        );
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
}
