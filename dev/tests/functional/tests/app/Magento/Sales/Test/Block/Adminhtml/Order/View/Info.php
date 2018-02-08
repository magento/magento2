<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for information about customer on order page
 *
 */
class Info extends Block
{
    /**
     * Customer email
     *
     * @var string
     */
    protected $email = '//th[text()="Email"]/following-sibling::*/a';

    /**
     * Customer group
     *
     * @var string
     */
    protected $group = '//th[text()="Customer Group"]/following-sibling::*';

    /**
     * Item options.
     *
     * @var string
     */
    protected $itemOptions = '//div[@class=\'product-sku-block\' and contains(normalize-space(.), \'{SKU}\')]'
        . '/following-sibling::*[@class="item-options"]';

    /**
     * Get email from the data inside block
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_rootElement->find($this->email, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get group from the data inside block
     *
     * @return string
     */
    public function getCustomerGroup()
    {
        return $this->_rootElement->find($this->group, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get Product options.
     *
     * @param string $sku
     * @return array
     */
    public function getProductOptions($sku)
    {
        $selector = str_replace('{SKU}', $sku, $this->itemOptions);
        $productOption = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
        $result = [];
        if ($productOption->isVisible()) {
            $keyItem = $productOption->getElements('dt');
            $valueItem = $productOption->getElements('dd');
            foreach ($keyItem as $key => $item) {
                $result[$item->getText()] = null;
                if (isset($valueItem[$key])) {
                    $result[$item->getText()] = $valueItem[$key]->getText();
                }
            }
        }

        return $result;
    }
}
