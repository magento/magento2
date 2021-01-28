<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

class ResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\Resolver
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appStateMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeMock;

    protected function setUp(): void
    {
        $this->designMock = $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class);
        $this->themeCollectionFactoryMock = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create']
        );
        $this->themeCollectionMock = $this->createMock(\Magento\Theme\Model\ResourceModel\Theme\Collection::class);
        $this->appStateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->themeMock = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);

        $this->model = new \Magento\Theme\Model\Theme\Resolver(
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
            'other_area' . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR . 'other_theme'
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
