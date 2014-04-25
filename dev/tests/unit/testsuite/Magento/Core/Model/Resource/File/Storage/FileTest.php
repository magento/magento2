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
namespace Magento\Core\Model\Resource\File\Storage;

/**
 * Class FileTest
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Resource\File\Storage\File
     */
    protected $storageFile;

    /**
     * @var \Magento\Core\Helper\File\Media|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryReadMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->filesystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryRead'),
            array(),
            '',
            false
        );
        $this->directoryReadMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            array('isDirectory', 'readRecursively'),
            array(),
            '',
            false
        );

        $this->storageFile = new \Magento\Core\Model\Resource\File\Storage\File(
            $this->filesystemMock,
            $this->loggerMock
        );
    }

    protected function tearDown()
    {
        unset($this->storageFile);
    }

    /**
     * test get storage data
     */
    public function testGetStorageData()
    {
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            $this->equalTo(\Magento\Framework\App\Filesystem::MEDIA_DIR)
        )->will(
            $this->returnValue($this->directoryReadMock)
        );

        $this->directoryReadMock->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->will(
            $this->returnValueMap(
                array(
                    array('/', true),
                    array('folder_one', true),
                    array('file_three.txt', false),
                    array('folder_one/.svn', false),
                    array('folder_one/file_one.txt', false),
                    array('folder_one/folder_two', true),
                    array('folder_one/folder_two/.htaccess', false),
                    array('folder_one/folder_two/file_two.txt', false)
                )
            )
        );

        $paths = array(
            'folder_one',
            'file_three.txt',
            'folder_one/.svn',
            'folder_one/file_one.txt',
            'folder_one/folder_two',
            'folder_one/folder_two/.htaccess',
            'folder_one/folder_two/file_two.txt'
        );
        sort($paths);
        $this->directoryReadMock->expects(
            $this->once()
        )->method(
            'readRecursively'
        )->with(
            $this->equalTo('/')
        )->will(
            $this->returnValue($paths)
        );

        $expected = array(
            'files' => array('file_three.txt', 'folder_one/file_one.txt', 'folder_one/folder_two/file_two.txt'),
            'directories' => array(
                array('name' => 'folder_one', 'path' => '/'),
                array('name' => 'folder_two', 'path' => 'folder_one')
            )
        );
        $actual = $this->storageFile->getStorageData();

        $this->assertEquals($expected, $actual);
    }
}
