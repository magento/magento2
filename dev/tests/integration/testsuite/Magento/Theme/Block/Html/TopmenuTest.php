<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Block\Html;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

class TopmenuTest extends TestCase
{
    public function testLinkDeclaredInDiPresentInTopMenu()
    {
        /**
         * @var $topmenu Topmenu
         */
        $topMenu = Bootstrap::getObjectManager()->get(Topmenu::class);
        $topMenuHtml = $topMenu->getHtml();
        $this->assertContains('<a href="/top-menu-additional-link"', $topMenuHtml);
    }
}
