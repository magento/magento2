<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product information tabs
 */
namespace Magento\Catalog\Block\Product\View;

/**
 * Class \Magento\Catalog\Block\Product\View\Tabs
 *
 * @since 2.0.0
 */
class Tabs extends \Magento\Framework\View\Element\Template
{
    /**
     * Configured tabs
     *
     * @var array
     * @since 2.0.0
     */
    protected $_tabs = [];

    /**
     * Add tab to the container
     *
     * @param string $alias
     * @param string $title
     * @param string $block
     * @param string $template
     * @param string $header
     * @return void
     * @since 2.0.0
     */
    public function addTab($alias, $title, $block, $template, $header = null)
    {
        if (!$title || !$block || !$template) {
            return;
        }

        $this->_tabs[] = ['alias' => $alias, 'title' => $title, 'header' => $header];

        $this->setChild($alias, $this->getLayout()->createBlock($block, $alias)->setTemplate($template));
    }

    /**
     * Return configured tabs
     *
     * @return array
     * @since 2.0.0
     */
    public function getTabs()
    {
        return $this->_tabs;
    }
}
