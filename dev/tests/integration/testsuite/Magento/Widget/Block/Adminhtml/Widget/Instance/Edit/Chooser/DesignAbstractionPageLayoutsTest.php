<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Design\ThemeInterfaceFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('adminhtml')]
class DesignAbstractionPageLayoutsTest extends TestCase
{
    public function testPageLayoutsGroupListsThemePageLayouts(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ThemeInterface $theme */
        $theme = $objectManager->get(ThemeInterfaceFactory::class)->create();
        $theme->load('Magento/luma', 'theme_path');
        $this->assertNotEmpty($theme->getId());

        /** @var DesignAbstraction $block */
        $block = $objectManager->get(LayoutInterface::class)->createBlock(DesignAbstraction::class);
        $block->setName('layout_handle')->setId('layout_handle')->setArea('frontend')->setTheme($theme->getId());

        $dom = new \DOMDocument();
        $dom->loadXML($block->toHtml());
        $pageLayouts = [];
        foreach ((new \DOMXPath($dom))->query('//optgroup[@label="Page Layouts"]/option') as $option) {
            $pageLayouts[] = $option->getAttribute('value');
        }

        foreach (['empty', '1column', '2columns-left', '2columns-right', '3columns'] as $expected) {
            $this->assertContains($expected, $pageLayouts);
        }
    }
}
