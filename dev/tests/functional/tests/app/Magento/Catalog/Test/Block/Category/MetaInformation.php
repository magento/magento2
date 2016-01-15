<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Category;

use Magento\Mtf\Block\Block;

/**
 * MetaInformation block on the category page.
 */
class MetaInformation extends Block
{
    /**
     * Locator for the page title.
     *
     * @var string
     */
    protected $title = 'title';

    /**
     * Get page title.
     *
     * @return string
     */
    public function getTitle()
    {
        $pageContent = new \SimpleXMLElement($this->browser->getHtmlSource());
        return (string)$pageContent->head->title;
    }
}
