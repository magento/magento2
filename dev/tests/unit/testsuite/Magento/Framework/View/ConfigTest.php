<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Config */
    protected $config;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject */
    protected $readerMock;

    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject */
    protected $repositoryMock;

    /** @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileSystemMock;

    /** @var \Magento\Framework\Config\FileIteratorFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileIteratorFactoryMock;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $directoryReadMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->directoryReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with($this->equalTo(DirectoryList::ROOT))
            ->will($this->returnValue($this->directoryReadMock));
        $this->repositoryMock = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->fileSystemMock = $this->getMock('Magento\Framework\View\FileSystem', [], [], '', false);
        $this->fileIteratorFactoryMock = $this->getMock('Magento\Framework\Config\FileIteratorFactory');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Config',
            [
                'moduleReader' => $this->readerMock,
                'filesystem' => $this->filesystemMock,
                'assetRepo' => $this->repositoryMock,
                'viewFileSystem' => $this->fileSystemMock,
                'fileIteratorFactory' => $this->fileIteratorFactoryMock
            ]
        );
    }

    public function testGetViewConfig()
    {
        $themeMock = $this->getMock(
            'Magento\Core\Model\Theme',
            ['getId', 'getCustomization', 'getCustomViewConfigPath'],
            [],
            '',
            false
        );
        $themeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue(2));
        $themeMock->expects($this->once())
            ->method('getCustomization')
            ->will($this->returnSelf());
        $themeMock->expects($this->once())
            ->method('getCustomViewConfigPath')
            ->will($this->returnValue(''));
        $params = ['themeModel' => $themeMock];
        $configFile = 'config.xml';
        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('updateDesignParams')
            ->with($this->equalTo($params))
            ->will($this->returnSelf());
        $iterator = $this->getMock('Magento\Framework\Config\FileIterator', [], [], '', false);
        $iterator->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([]));
        $this->readerMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->with($this->equalTo(basename(\Magento\Framework\View\ConfigInterface::CONFIG_FILE_NAME)))
            ->will($this->returnValue($iterator));
        $this->directoryReadMock->expects($this->once())
            ->method('isExist')
            ->with($this->anything())
            ->will($this->returnValue(true));
        $this->fileSystemMock->expects($this->once())
            ->method('getFilename')
            ->with($this->equalTo(\Magento\Framework\View\ConfigInterface::CONFIG_FILE_NAME), $params)
            ->will($this->returnValue($configFile));
        $this->directoryReadMock->expects($this->any())
            ->method('getRelativePath')
            ->with($this->equalTo($configFile))
            ->will($this->returnArgument(0));
        $xmlData = '<view><vars module="Magento_Catalog"><var name="test">1</var></vars></view>';
        $this->directoryReadMock->expects($this->once())
            ->method('readFile')
            ->with($this->equalTo($configFile))
            ->will($this->returnValue($xmlData));
        $this->assertInstanceOf('Magento\Framework\Config\View', $this->config->getViewConfig($params));
        // lazy load test
        $this->assertInstanceOf('Magento\Framework\Config\View', $this->config->getViewConfig($params));
    }
}
