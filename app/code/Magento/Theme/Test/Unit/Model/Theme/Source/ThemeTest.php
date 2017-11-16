<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Source;

use \Magento\Theme\Model\Theme\Source\Theme;

class ThemeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @true
     * @return void
     * @covers \Magento\Theme\Model\Theme\Source\Theme::__construct
     * @covers \Magento\Theme\Model\Theme\Source\Theme::getAllOptions
     */
    public function testGetAllOptions()
    {
        $expects = ['labels'];
        $label = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\Label::class)
            ->disableOriginalConstructor()
            ->getMock();
        $label->expects($this->once())
            ->method('getLabelsCollection')
            ->with(__('-- Please Select --'))
            ->willReturn($expects);

        /** @var $label \Magento\Framework\View\Design\Theme\Label */
        $object = new Theme($label);
        $this->assertEquals($expects, $object->getAllOptions());
    }
}
