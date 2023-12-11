<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System;

use Magento\Backend\Block\Widget\Button;
use Magento\CurrencySymbol\Block\Adminhtml\System\Currencysymbol;
use Magento\CurrencySymbol\Model\System\CurrencysymbolFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class CurrencysymbolTest extends TestCase
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
        $symbolSystemFactoryMock = $this->createPartialMock(
            CurrencysymbolFactory::class,
            ['create']
        );

        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->addMethods(['addChild'])
            ->onlyMethods(['toHtml'])
            ->getMockForAbstractClass();

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->getMockForAbstractClass(
            LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getBlock']
        );

        $layoutMock->expects($this->once())->method('getBlock')->willReturn($blockMock);

        $blockMock->expects($this->once())
            ->method('addChild')
            ->with(
                'save_button',
                Button::class,
                [
                    'label' => __('Save Currency Symbols'),
                    'class' => 'save primary save-currency-symbols',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#currency-symbols-form']],
                    ]
                ]
            );

        /** @var Currencysymbol $block */
        $block = $this->objectManagerHelper->getObject(
            Currencysymbol::class,
            [
                'symbolSystemFactory' => $symbolSystemFactoryMock,
                'layout' => $layoutMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
