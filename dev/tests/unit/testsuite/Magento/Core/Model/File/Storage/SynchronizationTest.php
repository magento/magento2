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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

class SynchronizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test fir synchronize method
     */
    public function testSynchronize()
    {
        $content = 'content';
        $relativeFileName = 'config.xml';
        $filePath = realpath(__DIR__ . '/_files/');

        $storageFactoryMock = $this->getMock(
            'Magento\Core\Model\File\Storage\DatabaseFactory',
            array('create', '_wakeup'),
            array(),
            '',
            false
        );
        $storageMock = $this->getMock(
            'Magento\Core\Model\File\Storage\Database',
            array('getContent', 'getId', 'loadByFilename', '__wakeup'),
            array(),
            '',
            false
        );
        $storageFactoryMock->expects($this->once())->method('create')->will($this->returnValue($storageMock));

        $storageMock->expects($this->once())->method('getContent')->will($this->returnValue($content));
        $storageMock->expects($this->once())->method('getId')->will($this->returnValue(true));
        $storageMock->expects($this->once())->method('loadByFilename');

        $file = $this->getMock(
            'Magento\Framework\Filesystem\File\Write',
            array('lock', 'write', 'unlock', 'close'),
            array(),
            '',
            false
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with($content);
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->getMock(
            'Magento\Framework\Filesystem\Direcoty\Write',
            array('openFile', 'getRelativePath'),
            array(),
            '',
            false
        );
        $directory->expects($this->once())->method('getRelativePath')->will($this->returnArgument(0));
        $directory->expects($this->once())->method('openFile')->with($filePath)->will($this->returnValue($file));
        $filesystem = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryWrite'),
            array(),
            '',
            false
        );
        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::PUB_DIR
        )->will(
            $this->returnValue($directory)
        );

        $model = new Synchronization($storageFactoryMock, $filesystem);
        $model->synchronize($relativeFileName, $filePath);
    }
}
