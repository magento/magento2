<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Customer\Products;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class ListProducts
 * Downloadable Products block
 */
class ListProducts extends Block
{
    /**
     * Link selector
     *
     * @var string
     */
    protected $link = '//a[contains(text(), "%s")]';

    /**
     * Open Link by title
     *
     * @param string $linkTitle
     * @return void
     */
    public function openLink($linkTitle)
    {
        $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get link url by title.
     *
     * @param string $title
     * @return string
     */
    public function getLinkUrl($title)
    {
        return $this->_rootElement->find(sprintf($this->link, $title), Locator::SELECTOR_XPATH)->getAttribute('href');
    }
}
