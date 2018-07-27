<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout\Pool as LayoutPool;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;

/**
 * Class Structure
 */
class Structure
{
    /**
     * @var LayoutPool
     */
    protected $layoutPool;

    /**
     * Constructor
     *
     * @param LayoutPool $layoutPool
     */
    public function __construct(LayoutPool $layoutPool)
    {
        $this->layoutPool = $layoutPool;
    }

    /**
     * Build component structure and retrieve
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function generate(UiComponentInterface $component)
    {
        /** @var LayoutInterface $layout */
        if (!$layoutDefinition = $component->getData('layout')) {
            $layoutDefinition = ['type' => 'generic'];
        }
        $layout = $this->layoutPool->create($layoutDefinition['type'], $layoutDefinition);

        return $layout->build($component);
    }
}
