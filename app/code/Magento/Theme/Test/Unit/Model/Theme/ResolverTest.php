<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\Resolver
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    protected function setUp()
    {
        $this->designMock = $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class);
        $this->themeCollectionFactoryMock = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->themeCollectionMock = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->appStateMock = $this->getMock(
            \Magento\Framework\App\State::class,
            [],
            [],
            '',
            false
        );
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
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects($this->never())->method('getArea');
        $this->designMock->expects($this->never())->method('getConfigurationDesignTheme');

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects($this->never())->method('create');

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithDesignDefaultArea()
    {
        $this->designMock->expects(
            $this->exactly(2)
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('design_area')
        );
        $this->designMock->expects($this->never())->method('getConfigurationDesignTheme');

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects($this->never())->method('create');

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('design_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithOtherAreaAndStringThemeId()
    {
        $this->designMock->expects(
            $this->once()
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('design_area')
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue('other_theme')
        );

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->themeCollectionMock)
        );

        $this->themeCollectionMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            'other_area' . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR . 'other_theme'
        )->will(
            $this->returnValue($this->themeMock)
        );

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('other_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithOtherAreaAndNumericThemeId()
    {
        $this->designMock->expects(
            $this->once()
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('design_area')
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue(12)
        );

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->themeCollectionMock)
        );

        $this->themeCollectionMock->expects(
            $this->once()
        )->method(
            'getItemById'
        )->with(
            12
        )->will(
            $this->returnValue($this->themeMock)
        );

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('other_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }
}
