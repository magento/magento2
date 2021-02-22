<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\View\Design\Theme\Label;

use Magento\Framework\View\Design\Theme\Label\ListInterface;
use Magento\Framework\View\Design\Theme\Label\Options;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /** @var Options */
    protected $model;

    /** @var ListInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $listMock;

    protected function setUp(): void
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
