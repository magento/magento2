<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Html;

use Magento\Catalog\ViewModel\SeoConfigTopMenu as TopMenuViewModel;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Block\Html\Topmenu;

class TopmenuTest extends \PHPUnit\Framework\TestCase
{
    /** @var Topmenu */
    private $block;

    protected function setUp(): void
    {
        $this->block = Bootstrap::getObjectManager()
            ->get(LayoutInterface::class)
            ->createBlock(Topmenu::class);
    }

    /**
     * Checks top menu template gets correct widget configuration.
     *
     * @magentoAppArea frontend
     */
    public function testGetHtml()
    {
        $this->block->setTemplate('Magento_Catalog::html/topmenu.phtml');
        $topMenuViewModel = Bootstrap::getObjectManager()->get(TopMenuViewModel ::class);
        $this->block->setData('viewModel', $topMenuViewModel);
        $html = $this->block->toHtml();
        $this->assertStringContainsString(
            '"useCategoryPathInUrl":0',
            $html,
            "The HTML does not contain the expected JSON fragment."
        );
    }
}
