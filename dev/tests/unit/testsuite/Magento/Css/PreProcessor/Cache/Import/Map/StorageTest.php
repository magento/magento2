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
namespace Magento\Css\PreProcessor\Cache\Import\Map;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Css\PreProcessor\Cache\Import\Map\Storage */
    protected $storage;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mapsDirectoryMock;

    protected function setUp()
    {
        $this->mapsDirectoryMock = $this->getMock(
            'Magento\Filesystem\Directory\WriteInterface',
            array(),
            array(),
            '',
            false
        );
        $this->mapsDirectoryMock->expects(
            $this->once()
        )->method(
            'isDirectory'
        )->with(
            $this->equalTo(\Magento\Css\PreProcessor\Cache\Import\Map\Storage::MAPS_DIR)
        )->will(
            $this->returnValue(false)
        );
        $this->mapsDirectoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo(\Magento\Css\PreProcessor\Cache\Import\Map\Storage::MAPS_DIR)
        )->will(
            $this->returnSelf()
        );


        $this->filesystemMock = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            $this->equalTo(\Magento\App\Filesystem::VAR_DIR)
        )->will(
            $this->returnValue($this->mapsDirectoryMock)
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->storage = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\Import\Map\Storage',
            array('filesystem' => $this->filesystemMock)
        );
    }

    /**
     * @param string $key
     * @param bool $isFile
     * @param string $mapFileName
     * @param bool|string $expected
     * @dataProvider loadDataProvider
     */
    public function testLoad($key, $isFile, $mapFileName, $expected)
    {
        $this->mapsDirectoryMock->expects(
            $this->once()
        )->method(
            'isFile'
        )->with(
            $this->equalTo($mapFileName)
        )->will(
            $this->returnValue($isFile)
        );
        if ($isFile) {
            $this->mapsDirectoryMock->expects(
                $this->once()
            )->method(
                'readFile'
            )->with(
                $this->equalTo($mapFileName)
            )->will(
                $this->returnValue($expected)
            );
        }
        $this->assertEquals($expected, $this->storage->load($key));
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return array(
            array('some_key', false, 'maps/less/3d70412c7e9ea2d96fa23d4f1f1f0a1c.ser', false),
            array('some_other_key', true, 'maps/less/26df19b852b11fb4b4b845134d13f6fa.ser', 'file_found')
        );
    }

    /**
     * @param string $key
     * @param string $mapFileName
     * @param array $data
     * @dataProvider saveDataProvider
     */
    public function testSave($key, $mapFileName, $data)
    {
        $this->mapsDirectoryMock->expects(
            $this->once()
        )->method(
            'writeFile'
        )->with(
            $this->equalTo($mapFileName),
            $this->equalTo($data)
        )->will(
            $this->returnSelf()
        );
        $this->assertEquals($this->storage, $this->storage->save($key, $data));
    }

    /**
     * @return array
     */
    public function saveDataProvider()
    {
        return array(
            array('some-key-to-save', 'maps/less/96760c434adbc683b503ca866784a17e.ser', array('data1', 'data2'))
        );
    }

    /**
     * @param string $key
     * @param string $mapFileName
     * @dataProvider deleteDataProvider
     */
    public function testDelete($key, $mapFileName)
    {
        $this->mapsDirectoryMock->expects(
            $this->once()
        )->method(
            'writeFile'
        )->with(
            $this->equalTo($mapFileName),
            $this->equalTo('')
        )->will(
            $this->returnSelf()
        );
        $this->assertEquals($this->storage, $this->storage->delete($key));
    }

    /**
     * @return array
     */
    public function deleteDataProvider()
    {
        return array(array('some-key-to-delete', 'maps/less/bf8aef83aab96deb7dbd66579b389794.ser'));
    }

    public function testClearMaps()
    {
        $this->mapsDirectoryMock->expects(
            $this->once()
        )->method(
            'delete'
        )->with(
            $this->equalTo(\Magento\Css\PreProcessor\Cache\Import\Map\Storage::MAPS_DIR)
        )->will(
            $this->returnSelf()
        );

        $this->assertEquals($this->storage, $this->storage->clearMaps());
    }
}
