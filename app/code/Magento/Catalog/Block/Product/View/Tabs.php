<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product information tabs
 */
namespace Magento\Catalog\Block\Product\View;

class Tabs extends \Magento\Framework\View\Element\Template
{
    /**
     * Configured tabs
     *
     * @var array
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
     */
    public function getTabs()
    {
        return $this->_tabs;
    }
}
