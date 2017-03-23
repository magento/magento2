<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\Create;

use Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Items\ItemProduct;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class Items
 * Adminhtml GiftMessage order create items block.
 */
class Items extends \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items
{
    /**
     * Item product.
     *
     * @var string
     */
    protected $itemProduct = '//tbody[*[td//*[normalize-space(text())="%s"]]]';

    /**
     * Get item product block.
     *
     * @param InjectableFixture $product
     * @return ItemProduct
     */
    public function getItemProduct(InjectableFixture $product)
    {
        return $this->blockFactory->create(
            \Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Items\ItemProduct::class,
            [
                'element' => $this->browser->find(
                    sprintf($this->itemProduct, $product->getName()),
                    Locator::SELECTOR_XPATH
                )
            ]
        );
    }
}
