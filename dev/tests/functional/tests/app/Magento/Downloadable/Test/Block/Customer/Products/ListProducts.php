<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Customer\Products;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

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
}
