<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SplitTest extends TestCase
{
    public function testHasSplit()
    {
        $objectManagerHelper = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);
        /** @var SplitButton $block */
        $block = $objectManagerHelper->getObject(SplitButton::class);
        $this->assertTrue($block->hasSplit());
        $block->setData('has_split', false);
        $this->assertFalse($block->hasSplit());
        $block->setData('has_split', true);
        $this->assertTrue($block->hasSplit());
    }
}
