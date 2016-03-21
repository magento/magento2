<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Sidebar block.
 */
abstract class Sidebar extends Block
{
    /**
     * 'Add to order' checkbox.
     *
     * @var string
     */
    protected $addToOrder = './/tr[td[.="%s"]]//input[contains(@name,"add")]';

    /**
     * 'Add to order' checkbox.
     *
     * @var string
     */
    protected $addToOrderProductName = './/tr/td[.="%s"]';

    /**
     * Add productz to order.
     *
     * @param array $products
     * @return void
     */
    public function addProductsToOrder(array $products)
    {
        foreach ($products as $product) {
            $name = $product->getName();
            $this->_rootElement->find(sprintf($this->addToOrderProductName, $name), Locator::SELECTOR_XPATH)->click();
            $this->_rootElement->click();
            $this->_rootElement->find(sprintf($this->addToOrder, $name), Locator::SELECTOR_XPATH, 'checkbox')
                ->setValue('Yes');
        }
    }
}
