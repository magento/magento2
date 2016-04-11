<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    /** @var \Magento\Framework\View\Asset\Repository | \PHPUnit_Framework_MockObject_MockObject */
    protected $repositoryMock;

    /**
     * @var \Magento\Framework\Config\ViewFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewConfigFactoryMock;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->viewConfigFactoryMock = $this->getMock('Magento\Framework\Config\ViewFactory', [], [], '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Config',
            [
                'assetRepo' => $this->repositoryMock,
                'viewConfigFactory' => $this->viewConfigFactoryMock
            ]
        );
    }

    public function testGetViewConfig()
    {
        $themeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['getCode'],
            [],
            '',
            false
        );
        $themeMock->expects($this->atLeastOnce())
            ->method('getCode')
            ->will($this->returnValue(2));
        $params = ['themeModel' => $themeMock];
        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('updateDesignParams')
            ->with($this->equalTo($params))
            ->will($this->returnSelf());
        $configViewMock = $this->getMock('Magento\Framework\Config\View', [], [], '', false);
        $this->viewConfigFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configViewMock);
        $this->assertInstanceOf('Magento\Framework\Config\View', $this->config->getViewConfig($params));
        // lazy load test
        $this->assertInstanceOf('Magento\Framework\Config\View', $this->config->getViewConfig($params));
    }
}
