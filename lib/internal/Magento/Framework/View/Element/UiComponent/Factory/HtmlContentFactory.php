<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Factory;

use Magento\Framework\View\Layout;

/**
 * Class \Magento\Framework\View\Element\UiComponent\Factory\HtmlContentFactory
 *
 */
class HtmlContentFactory implements ComponentFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create(array &$bundleComponents, array $arguments = [])
    {
        if (!isset($arguments['context']) || !isset($bundleComponents['arguments']['block']['name'])) {
            return false;
        }
        /** @var Layout $layout */
        $layout = $arguments['context']->getPageLayout();

        $block = $layout->getBlock($bundleComponents['arguments']['block']['name']);
        $bundleComponents['arguments']['block'] = $block;
        return true;
    }
}
