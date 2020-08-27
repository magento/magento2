<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System;

use Magento\Backend\Block\Widget\Button;
use Magento\CurrencySymbol\Block\Adminhtml\System\Currency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * Object manager helper
     *
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
    }

    protected function tearDown(): void
    {
        unset($this->objectManagerHelper);
    }

    public function testPrepareLayout()
    {
        $childBlockMock = $this->getMockBuilder(BlockInterface::class)
            ->addMethods(['addChild'])
            ->onlyMethods(['toHtml'])
            ->getMockForAbstractClass();

        $blockMock = $this->getMockForAbstractClass(BlockInterface::class);

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->getMockForAbstractClass(
            LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getBlock', 'createBlock']
        );

        $layoutMock->expects($this->any())->method('getBlock')->willReturn($childBlockMock);
        $layoutMock->expects($this->any())->method('createBlock')->willReturn($blockMock);

        $childBlockMock->expects($this->at(0))
            ->method('addChild')
            ->with(
                'save_button',
                Button::class,
                [
                    'label' => __('Save Currency Rates'),
                    'class' => 'save primary save-currency-rates',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#rate-form']],
                    ]
                ]
            );

        $childBlockMock->expects($this->at(1))
            ->method('addChild')
            ->with(
                'options_button',
                Button::class,
                ['label' => __('Options'), 'onclick' => 'setLocation(\'\')']
            );

        $childBlockMock->expects($this->at(2))
            ->method('addChild')
            ->with(
                'reset_button',
                Button::class,
                ['label' => __('Reset'), 'onclick' => 'document.location.reload()', 'class' => 'reset']
            );

        /** @var \Magento\CurrencySymbol\Block\Adminhtml\System\Currency $block */
        $block = $this->objectManagerHelper->getObject(
            Currency::class,
            [
                'layout' => $layoutMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
