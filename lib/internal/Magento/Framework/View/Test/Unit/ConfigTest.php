<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConfigTest extends \PHPUnit\Framework\TestCase
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
        $this->repositoryMock = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->viewConfigFactoryMock = $this->createMock(\Magento\Framework\Config\ViewFactory::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Config::class,
            [
                'assetRepo' => $this->repositoryMock,
                'viewConfigFactory' => $this->viewConfigFactoryMock
            ]
        );
    }

    public function testGetViewConfig()
    {
        $themeCode = 'area/theme';

        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['getFullPath']);
        $themeMock->expects($this->atLeastOnce())
            ->method('getFullPath')
            ->will($this->returnValue($themeCode));
        $params = [
            'themeModel' => $themeMock,
            'area'       => 'frontend'
        ];
        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('updateDesignParams')
            ->with($this->equalTo($params))
            ->will($this->returnSelf());
        $configViewMock = $this->createMock(\Magento\Framework\Config\View::class);
        $this->viewConfigFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configViewMock);
        $this->assertInstanceOf(\Magento\Framework\Config\View::class, $this->config->getViewConfig($params));
        // lazy load test
        $this->assertInstanceOf(\Magento\Framework\Config\View::class, $this->config->getViewConfig($params));
    }
}
