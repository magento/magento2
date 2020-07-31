<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\State;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme\Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    /**
     * @var Resolver
     */
    protected $model;

    /**
     * @var DesignInterface|MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory|MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var State|MockObject
     */
    protected $appStateMock;

    /**
     * @var Collection|MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var ThemeInterface|MockObject
     */
    protected $themeMock;

    protected function setUp(): void
    {
        $this->designMock = $this->getMockForAbstractClass(DesignInterface::class);
        $this->themeCollectionFactoryMock = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create']
        );
        $this->themeCollectionMock = $this->createMock(Collection::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->themeMock = $this->getMockForAbstractClass(ThemeInterface::class);

        $this->model = new Resolver(
            $this->appStateMock,
            $this->designMock,
            $this->themeCollectionFactoryMock
        );
    }

    public function testGetByAreaWithThemeDefaultArea()
    {
        $this->designMock->expects(
            $this->exactly(2)
        )->method(
            'getDesignTheme'
        )->willReturn(
            $this->themeMock
        );
        $this->designMock->expects($this->never())->method('getArea');
        $this->designMock->expects($this->never())->method('getConfigurationDesignTheme');

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'theme_area'
        );

        $this->themeCollectionFactoryMock->expects($this->never())->method('create');

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->willReturn(
            'theme_area'
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithDesignDefaultArea()
    {
        $this->designMock->expects(
            $this->exactly(2)
        )->method(
            'getDesignTheme'
        )->willReturn(
            $this->themeMock
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'design_area'
        );
        $this->designMock->expects($this->never())->method('getConfigurationDesignTheme');

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'theme_area'
        );

        $this->themeCollectionFactoryMock->expects($this->never())->method('create');

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->willReturn(
            'design_area'
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithOtherAreaAndStringThemeId()
    {
        $this->designMock->expects(
            $this->once()
        )->method(
            'getDesignTheme'
        )->willReturn(
            $this->themeMock
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'design_area'
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->willReturn(
            'other_theme'
        );

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'theme_area'
        );

        $this->themeCollectionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->themeCollectionMock
        );

        $this->themeCollectionMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            'other_area' . ThemeInterface::PATH_SEPARATOR . 'other_theme'
        )->willReturn(
            $this->themeMock
        );

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->willReturn(
            'other_area'
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithOtherAreaAndNumericThemeId()
    {
        $this->designMock->expects(
            $this->once()
        )->method(
            'getDesignTheme'
        )->willReturn(
            $this->themeMock
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'design_area'
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->willReturn(
            12
        );

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->willReturn(
            'theme_area'
        );

        $this->themeCollectionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->themeCollectionMock
        );

        $this->themeCollectionMock->expects(
            $this->once()
        )->method(
            'getItemById'
        )->with(
            12
        )->willReturn(
            $this->themeMock
        );

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->willReturn(
            'other_area'
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }
}
