<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;

/**
 * Pricing render's layout model
 * @since 2.0.0
 */
class Layout
{
    /**
     * Layout Interface
     *
     * @var LayoutInterface
     * @since 2.0.0
     */
    protected $layout;

    /**
     * Constructor
     *
     * @param LayoutFactory $layoutFactory
     * @param LayoutInterface $generalLayout
     * @since 2.0.0
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        \Magento\Framework\View\LayoutInterface $generalLayout
    ) {
        $this->layout = $layoutFactory->create(['cacheable' => $generalLayout->isCacheable()]);
    }

    /**
     * Add handle(s) to layout
     *
     * @param string|string[] $handle
     * @return void
     * @since 2.0.0
     */
    public function addHandle($handle)
    {
        $this->layout->getUpdate()->addHandle($handle);
    }

    /**
     * Load layout
     *
     * @return void
     * @since 2.0.0
     */
    public function loadLayout()
    {
        $this->layout->getUpdate()->load();
        $this->layout->generateXml();
        $this->layout->generateElements();
    }

    /**
     * Obtain block object
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    public function getBlock($name)
    {
        return $this->layout->getBlock($name);
    }
}
