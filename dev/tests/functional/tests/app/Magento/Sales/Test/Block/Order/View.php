<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Order;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class View
 * View block on order's view page
 */
class View extends Block
{
    /**
     * Item block
     *
     * @var string
     */
    protected $itemBlock = '//*[@class="order-title" and contains(.,"%d")]';

    /**
     * Content block
     *
     * @var string
     */
    protected $content = '//following-sibling::div[contains(@class,"order-items")][1]';

    /**
     * Link xpath selector
     *
     * @var string
     */
    protected $link = '//*[contains(@class,"order-links")]//a[normalize-space(.)="%s"]';

    /**
     * Get item block
     *
     * @param int $id [optional]
     * @return Items
     */
    public function getItemBlock($id = null)
    {
        $selector = ($id === null) ? $this->content : sprintf($this->itemBlock, $id) . $this->content;
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Order\Items',
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Open link by name
     *
     * @param string $name
     * @return void
     */
    public function openLinkByName($name)
    {
        $this->_rootElement->find(sprintf($this->link, $name), Locator::SELECTOR_XPATH)->click();
    }
}
