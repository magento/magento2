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
 * @since 2.2.0
 */
class HtmlContentFactory implements ComponentFactoryInterface
{
    /**
     * @inheritdoc
     * @since 2.2.0
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
