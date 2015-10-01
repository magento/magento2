<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Page\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Page\Source\Theme
     */
    protected $theme;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Design\Theme\Label\ListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeListMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->themeListMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\Theme\Label\ListInterface'
        );

        $this->theme = $this->objectManagerHelper->getObject(
            'Magento\Cms\Model\Page\Source\Theme',
            [
                'themeList' => $this->themeListMock
            ]
        );
    }

    public function testToOptionArray()
    {
        $labels = [
            [
                'value' => '3',
                'label' => 'Magento Blank'
            ],
            [
                'value' => '4',
                'label' => 'Magento Luma'
            ]
        ];
        $expectsResult = [
            [
                'label' => '',
                'value' => ''
            ],
            [
                'label' => 'Magento Blank',
                'value' => '3'
            ],
            [
                'label' => 'Magento Luma',
                'value' => '4'
            ]
        ];
        $this->themeListMock->expects($this->once())->method('getLabels')->willReturn($labels);

        $this->assertEquals($expectsResult, $this->theme->toOptionArray());
    }
}
