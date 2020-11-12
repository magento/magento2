<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\CurrencySymbol\Block\Adminhtml\System\Currency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\UrlInterface;

class CurrencyTest extends TestCase
{
    /**
     * Stub currency option link url
     */
    const STUB_OPTION_LINK_URL = 'https://localhost/admin/system_config/edit/section/currency#currency_options-link';

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

        $contextMock = $this->createMock(Context::class);
        $urlBuilderMock = $this->createMock(UrlInterface::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($urlBuilderMock);

        $urlBuilderMock->expects($this->once())->method('getUrl')->with(
            'adminhtml/system_config/edit',
            [
                'section' => 'currency',
                '_fragment' => 'currency_options-link'
            ]
        )->willReturn(self::STUB_OPTION_LINK_URL);

        $childBlockMock->expects($this->at(1))
            ->method('addChild')
            ->with(
                'options_button',
                Button::class,
                ['label' => __('Options'), 'onclick' => 'setLocation(\''.self::STUB_OPTION_LINK_URL.'\')']
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
                'layout' => $layoutMock,
                'context' => $contextMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
