<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\View\Design\Theme\Label;

use Magento\Framework\View\Design\Theme\Label\ListInterface;
use Magento\Framework\View\Design\Theme\Label\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Options */
    protected $model;

    /** @var ListInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $listMock;

    protected function setUp()
    {
        $this->listMock = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\Label\ListInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Options($this->listMock);
    }

    public function testToOptionArray()
    {
        $list = [
            ['value' => 44, 'label' => 'label'],
        ];

        $this->listMock->expects($this->once())
            ->method('getLabels')
            ->willReturn($list);

        $this->assertEquals($list, $this->model->toOptionArray());
    }
}
