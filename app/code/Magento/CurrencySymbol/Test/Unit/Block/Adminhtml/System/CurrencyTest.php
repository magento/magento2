<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System;

class CurrencyTest extends \PHPUnit_Framework_TestCase
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
        $childBlockMock = $this->getMock(
            'Magento\Framework\View\Element\BlockInterface',
            ['addChild', 'toHtml'],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\BlockInterface',
            [],
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
            ['getBlock', 'createBlock']
        );

        $layoutMock->expects($this->any())->method('getBlock')->willReturn($childBlockMock);
        $layoutMock->expects($this->any())->method('createBlock')->willReturn($blockMock);

        $childBlockMock->expects($this->at(0))
            ->method('addChild')
            ->with(
                'save_button',
                'Magento\Backend\Block\Widget\Button',
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
                'Magento\Backend\Block\Widget\Button',
                ['label' => __('Options'), 'onclick' => 'setLocation(\'\')']
            );

        $childBlockMock->expects($this->at(2))
            ->method('addChild')
            ->with(
                'reset_button',
                'Magento\Backend\Block\Widget\Button',
                ['label' => __('Reset'), 'onclick' => 'document.location.reload()', 'class' => 'reset']
            );

        /** @var $block \Magento\CurrencySymbol\Block\Adminhtml\System\Currency */
        $block = $this->objectManagerHelper->getObject(
            'Magento\CurrencySymbol\Block\Adminhtml\System\Currency',
            [
                'layout' => $layoutMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
