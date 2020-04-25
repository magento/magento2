<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\View\Design\Theme\Label;

use Magento\Framework\View\Design\Theme\Label\ListInterface;
use Magento\Framework\View\Design\Theme\Label\Options;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /** @var Options */
    protected $model;

    /** @var ListInterface|MockObject */
    protected $listMock;

    protected function setUp(): void
    {
        $this->listMock = $this->getMockBuilder(ListInterface::class)
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
