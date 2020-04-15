<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SplitTest extends TestCase
{
    public function testHasSplit()
    {
        $objectManagerHelper = new ObjectManager($this);
        /** @var SplitButton $block */
        $block = $objectManagerHelper->getObject(SplitButton::class);
        $this->assertSame(true, $block->hasSplit());
        $block->setData('has_split', false);
        $this->assertSame(false, $block->hasSplit());
        $block->setData('has_split', true);
        $this->assertSame(true, $block->hasSplit());
    }
}
