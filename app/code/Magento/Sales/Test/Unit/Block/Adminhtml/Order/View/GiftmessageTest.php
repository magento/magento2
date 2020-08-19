<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View;

use Magento\Framework\DataObject;
use Magento\Sales\Block\Adminhtml\Order\View\Giftmessage;
use PHPUnit\Framework\TestCase;

class GiftmessageTest extends TestCase
{
    public function testGetSaveButtonHtml()
    {
        $item = new DataObject();
        $expectedHtml = 'some_value';

        /** @var Giftmessage $block */
        $block = $this->createPartialMock(
            Giftmessage::class,
            ['getChildBlock', 'getChildHtml']
        );
        $block->setEntity(new DataObject());
        $block->expects($this->once())->method('getChildBlock')->with('save_button')->willReturn($item);
        $block->expects(
            $this->once()
        )->method(
            'getChildHtml'
        )->with(
            'save_button'
        )->willReturn(
            $expectedHtml
        );

        $this->assertEquals($expectedHtml, $block->getSaveButtonHtml());
        $this->assertNotEmpty($item->getOnclick());
    }
}
