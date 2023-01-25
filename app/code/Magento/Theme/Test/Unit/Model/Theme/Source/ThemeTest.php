<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme\Source;

use Magento\Framework\View\Design\Theme\Label;
use Magento\Theme\Model\Theme\Source\Theme;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /**
     * @return void
     * @covers \Magento\Theme\Model\Theme\Source\Theme::__construct
     * @covers \Magento\Theme\Model\Theme\Source\Theme::getAllOptions
     */
    public function testGetAllOptions()
    {
        $expects = ['labels'];
        $label = $this->getMockBuilder(Label::class)
            ->disableOriginalConstructor()
            ->getMock();
        $label->expects($this->once())
            ->method('getLabelsCollection')
            ->with(__('-- Please Select --'))
            ->willReturn($expects);

        /** @var Label $label */
        $object = new Theme($label);
        $this->assertEquals($expects, $object->getAllOptions());
    }
}
