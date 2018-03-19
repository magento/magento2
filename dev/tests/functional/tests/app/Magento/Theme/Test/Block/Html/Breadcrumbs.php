<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Block\Html;

use Magento\Mtf\Block\Block;

/**
 * Page breadcrumbs block.
 */
class Breadcrumbs extends Block
{
    /**
     * Locator for crumb item.
     *
     * @var string
     */
    private $crumbSelector = '.items > li';

    /**
     * Get breadcrumbs content of current page.
     *
     * @return string
     */
    public function getText()
    {
        return $this->_rootElement->getText();
    }

    /**
     * Returns list of breadcrumb items.
     *
     * @return array
     */
    public function getCrumbs()
    {
        $crumbs = $this->_rootElement->getElements($this->crumbSelector);

        $result = [];

        foreach ($crumbs as $crumb) {
            $result[] = $crumb->getText();
        }

        return $result;
    }
}
