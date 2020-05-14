<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Control;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing SplitButton widget
 *
 * @magentoAppArea frontend
 */
class SplitButtonTest extends TestCase
{

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->layout = $objectManager->get(LayoutInterface::class);
    }

    /**
     * Create the block.
     *
     * @return SplitButton
     */
    private function createBlock(): SplitButton
    {
        /** @var SplitButton $block */
        $block = $this->layout->createBlock(SplitButton::class, 'button_block');
        $block->setLayout($this->layout);

        return $block;
    }

    /**
     * Test resulting button HTML.
     *
     * @return void
     */
    public function testToHtml(): void
    {
        $block = $this->createBlock();
        $block->addData(
            [
                'title' => 'Split button control',
                'label' => 'Split button control',
                'has_split' => true,
                'button_class' => 'aclass',
                'id' => 'split-button',
                'disabled' => false,
                'class' => 'aclass',
                'data_attribute' => ['bind' => ['var' => 'val']],
                'id_hard' => 'split-button',
                'options' => [
                    [
                        'id' => 'an-option',
                        'disabled' => false,
                        'title' => 'An option',
                        'label' => 'An option',
                        'onclick' => $onclick = 'console.log("option")',
                        'style' => 'width: 100px'
                    ]
                ]
            ]
        );

        $html = $block->toHtml();
        $this->assertStringContainsString('<button ', $html);
        $this->assertStringContainsString('<span>Split button control</span>', $html);
        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertStringNotContainsString('style=', $html);
        $this->assertMatchesRegularExpression('/\<script.*?\>.*?' . preg_quote($onclick) . '.*?\<\/script\>/ims', $html);
        $this->assertStringContainsString('width', $html);
        $this->assertStringContainsString('100px', $html);
    }
}
