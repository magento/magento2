<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Helper;

/**
 * Helper to move images from tmp to catalog directory
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Config
     */
    protected $viewConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\DesignInterface
     */
    protected $currentThemeMock;

    /**
     * @var \Magento\ProductVideo\Helper\Media|\Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $mediaHelperObject;

    /**
     * @var array
     */
    protected $videoConfig;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->viewConfigMock = $this->getMock(
            '\Magento\Framework\View\Config',
            ['getMediaAttributes', 'getViewConfig'],
            [],
            '',
            false
        );

        $this->viewConfigMock
            ->expects($this->atLeastOnce())
            ->method('getViewConfig')
            ->willReturn($this->viewConfigMock);

        $this->themeCustomization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            [],
            [],
            '',
            false
        );
        $themeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );
        $themeMock->expects(
            $this->any()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($this->themeCustomization)
        );

        $this->currentThemeMock = $this->getMock('Magento\Framework\View\DesignInterface');
        $this->currentThemeMock->expects($this->any())->method('getDesignTheme')->will($this->returnValue($themeMock));

        $this->mediaHelperObject = $objectManager->getObject(
            '\Magento\ProductVideo\Helper\Media',
            [
                'configInterface' => $this->viewConfigMock,
                'designInterface' => $this->currentThemeMock,
            ]
        );

    }

    public function dataForVideoPlay()
    {
        return [
            [
                1,
            ],
            [
                0,
            ],
        ];
    }

    public function dataForVideoStop()
    {
        return [
            [
                1,
            ],
            [
                0,
            ],
        ];
    }

    public function dataForVideoBackground()
    {
        return [
            [
                '[255, 255, 255]',
            ],
            [
                '[0, 0, 0]',
            ],
        ];
    }

    /**
     * @dataProvider dataForVideoPlay
     */
    public function testGetPlayIfBaseAttribute($expectedResult)
    {
        $this->viewConfigMock->expects($this->once())->method('getMediaAttributes')->willReturn($expectedResult);
        $this->mediaHelperObject->getPlayIfBaseAttribute();
    }

    /**
     * @dataProvider dataForVideoStop
     */
    public function testGetShowRelatedAttribute($expectedResult)
    {
        $this->viewConfigMock->expects($this->once())->method('getMediaAttributes')->willReturn($expectedResult);
        $this->mediaHelperObject->getShowRelatedAttribute();
    }

    /**
     * @dataProvider dataForVideoBackground
     */
    public function testGetVideoAutoRestartAttribute($expectedResult)
    {
        $this->viewConfigMock->expects($this->once())->method('getMediaAttributes')->willReturn($expectedResult);
        $this->mediaHelperObject->getVideoAutoRestartAttribute();
    }
}
