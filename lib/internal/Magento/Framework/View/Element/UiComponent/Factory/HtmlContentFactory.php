<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent\Factory;

use Magento\Framework\View\Layout;

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
