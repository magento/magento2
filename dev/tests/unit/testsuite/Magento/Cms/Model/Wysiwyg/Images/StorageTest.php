<?php
namespace Magento\Cms\Model\Wysiwyg\Images;

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
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Directory paths samples
     */
    const STORAGE_ROOT_DIR = '/storage/root/dir';

    const INVALID_DIRECTORY_OVER_ROOT = '/storage/some/another/dir';

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    protected $_model = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageHelperMock;

    /**
     * @var array()
     */
    protected $_resizeParameters;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageCollectionFactoryMock;

    /**
     * @var \Magento\Core\Model\File\Storage\FileFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageFileFactoryMock;

    /**
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageDatabaseFactoryMock;

    /**
     * @var \Magento\Core\Model\File\Storage\Directory\DatabaseFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directoryDatabaseFactoryMock;

    /**
     * @var \Magento\Core\Model\File\UploaderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_uploaderFactoryMock;

    /**
     * @var \Magento\Backend\Model\Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \Magento\Backend\Model\Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendUrlMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directoryMock;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_driverMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->_driverMock = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\DriverInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getRealPath')
        );
        $this->_driverMock->expects($this->any())->method('getRealPath')->will($this->returnArgument(0));

        $this->_directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('delete', 'getDriver'),
            array(),
            '',
            false
        );
        $this->_directoryMock->expects(
            $this->any()
        )->method(
            'getDriver'
        )->will(
            $this->returnValue($this->_driverMock)
        );

        $this->_filesystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryWrite'),
            array(),
            '',
            false
        );
        $this->_filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::MEDIA_DIR
        )->will(
            $this->returnValue($this->_directoryMock)
        );

        $this->_adapterFactoryMock = $this->getMock(
            'Magento\Framework\Image\AdapterFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_imageHelperMock = $this->getMock(
            'Magento\Cms\Helper\Wysiwyg\Images',
            array('getStorageRoot'),
            array(),
            '',
            false
        );
        $this->_imageHelperMock->expects(
            $this->any()
        )->method(
            'getStorageRoot'
        )->will(
            $this->returnValue(self::STORAGE_ROOT_DIR)
        );

        $this->_resizeParameters = array('width' => 100, 'height' => 50);

        $this->_storageCollectionFactoryMock = $this->getMock(
            'Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory'
        );
        $this->_storageFileFactoryMock = $this->getMock('Magento\Core\Model\File\Storage\FileFactory');
        $this->_storageDatabaseFactoryMock = $this->getMock('Magento\Core\Model\File\Storage\DatabaseFactory');
        $this->_directoryDatabaseFactoryMock = $this->getMock(
            'Magento\Core\Model\File\Storage\Directory\DatabaseFactory'
        );
        $this->_uploaderFactoryMock = $this->getMock('Magento\Core\Model\File\UploaderFactory');
        $this->_sessionMock = $this->getMock('Magento\Backend\Model\Session', array(), array(), '', false);
        $this->_backendUrlMock = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Cms\Model\Wysiwyg\Images\Storage',
            array(
                'session' => $this->_sessionMock,
                'backendUrl' => $this->_backendUrlMock,
                'cmsWysiwygImages' => $this->_imageHelperMock,
                'coreFileStorageDb' => $this->getMock(
                    'Magento\Core\Helper\File\Storage\Database',
                    array(),
                    array(),
                    '',
                    false
                ),
                'filesystem' => $this->_filesystemMock,
                'imageFactory' => $this->_adapterFactoryMock,
                'assetRepo' => $this->getMock('Magento\Framework\View\Asset\Repository', array(), array(), '', false),
                'storageCollectionFactory' => $this->_storageCollectionFactoryMock,
                'storageFileFactory' => $this->_storageFileFactoryMock,
                'storageDatabaseFactory' => $this->_storageDatabaseFactoryMock,
                'directoryDatabaseFactory' => $this->_directoryDatabaseFactoryMock,
                'uploaderFactory' => $this->_uploaderFactoryMock,
                'resizeParameters' => $this->_resizeParameters
            )
        );
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::getResizeWidth
     */
    public function testGetResizeWidth()
    {
        $this->assertEquals(100, $this->_model->getResizeWidth());
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::getResizeHeight
     */
    public function testGetResizeHeight()
    {
        $this->assertEquals(50, $this->_model->getResizeHeight());
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::deleteDirectory
     */
    public function testDeleteDirectoryOverRoot()
    {
        $this->setExpectedException(
            '\Magento\Framework\Model\Exception',
            sprintf('Directory %s is not under storage root path.', self::INVALID_DIRECTORY_OVER_ROOT)
        );
        $this->_model->deleteDirectory(self::INVALID_DIRECTORY_OVER_ROOT);
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::deleteDirectory
     */
    public function testDeleteRootDirectory()
    {
        $this->setExpectedException(
            '\Magento\Framework\Model\Exception',
            sprintf('We cannot delete root directory %s.', self::STORAGE_ROOT_DIR)
        );
        $this->_model->deleteDirectory(self::STORAGE_ROOT_DIR);
    }
}
