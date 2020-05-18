<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\Config\View;
use Magento\Framework\Config\ViewFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Config;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @var Config */
    protected $config;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Repository|MockObject */
    protected $repositoryMock;

    /**
     * @var ViewFactory|MockObject
     */
    protected $viewConfigFactoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(Repository::class);
        $this->viewConfigFactoryMock = $this->createMock(ViewFactory::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'assetRepo' => $this->repositoryMock,
                'viewConfigFactory' => $this->viewConfigFactoryMock
            ]
        );
    }

    public function testGetViewConfig()
    {
        $themeCode = 'area/theme';

        $themeMock = $this->createPartialMock(Theme::class, ['getFullPath']);
        $themeMock->expects($this->atLeastOnce())
            ->method('getFullPath')
            ->willReturn($themeCode);
        $params = [
            'themeModel' => $themeMock,
            'area'       => 'frontend'
        ];
        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('updateDesignParams')
            ->with($params)->willReturnSelf();
        $configViewMock = $this->createMock(View::class);
        $this->viewConfigFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configViewMock);
        $this->assertInstanceOf(View::class, $this->config->getViewConfig($params));
        // lazy load test
        $this->assertInstanceOf(View::class, $this->config->getViewConfig($params));
    }
}
