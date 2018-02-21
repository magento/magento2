<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System;

class CurrencysymbolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    protected function tearDown()
    {
        unset($this->objectManagerHelper);
    }

    public function testPrepareLayout()
    {
        $symbolSystemFactoryMock = $this->getMock(
            'Magento\CurrencySymbol\Model\System\CurrencysymbolFactory',
            ['create'],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\BlockInterface',
            ['addChild', 'toHtml'],
            [],
            '',
            false
        );

        /** @var $layoutMock \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
        $layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
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
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Save Currency Symbols'),
                    'class' => 'save primary save-currency-symbols',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#currency-symbols-form']],
                    ]
                ]
            );

        /** @var $block \Magento\CurrencySymbol\Block\Adminhtml\System\Currencysymbol */
        $block = $this->objectManagerHelper->getObject(
            'Magento\CurrencySymbol\Block\Adminhtml\System\Currencysymbol',
            [
                'symbolSystemFactory' => $symbolSystemFactoryMock,
                'layout' => $layoutMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
