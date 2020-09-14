<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Item\Price;

use Magento\Checkout\Block\Item\Price\Renderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->renderer = $objectManagerHelper->getObject(
            Renderer::class
        );
    }

    public function testSetItem()
    {
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->renderer->setItem($item);
        $this->assertEquals($item, $this->renderer->getItem());
    }
}
