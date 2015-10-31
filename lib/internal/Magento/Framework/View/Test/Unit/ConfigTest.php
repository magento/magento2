<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Config */
    protected $config;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Module\Dir\Reader | \PHPUnit_Framework_MockObject_MockObject */
    protected $readerMock;

    /** @var \Magento\Framework\Filesystem | \PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\Framework\View\Asset\Repository | \PHPUnit_Framework_MockObject_MockObject */
    protected $repositoryMock;

    /** @var \Magento\Framework\View\FileSystem | \PHPUnit_Framework_MockObject_MockObject */
    protected $fileSystemMock;

    /** @var \Magento\Framework\Config\FileIteratorFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $fileIteratorFactoryMock;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface | \PHPUnit_Framework_MockObject_MockObject */
    protected $directoryReadMock;

    /**
     * @var \Magento\Framework\Config\ViewFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewConfigFactoryMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->directoryReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->repositoryMock = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->fileIteratorFactoryMock = $this->getMock(
            'Magento\Framework\Config\FileIteratorFactory',
            [],
            [],
            '',
            false
        );
        $this->viewConfigFactoryMock = $this->getMock('Magento\Framework\Config\ViewFactory', [], [], '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Config',
            [
                'assetRepo' => $this->repositoryMock,
                'fileIteratorFactory' => $this->fileIteratorFactoryMock,
                'viewFactory' => $this->viewConfigFactoryMock
            ]
        );
    }

    public function testGetViewConfig()
    {
        $themeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['getCustomViewConfigPath'],
            [],
            '',
            false
        );
        $params = ['themeModel' => $themeMock];
        $configViewMock = $this->getMock('Magento\Framework\Config\View', [], [], '', false);
        $this->viewConfigFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configViewMock);
        $this->assertInstanceOf('Magento\Framework\Config\View', $this->config->getViewConfig($params));
        // lazy load test
        $this->assertInstanceOf('Magento\Framework\Config\View', $this->config->getViewConfig($params));
    }
}
