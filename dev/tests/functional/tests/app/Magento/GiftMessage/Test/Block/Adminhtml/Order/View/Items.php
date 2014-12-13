<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\View;

use Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items\ItemProduct;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\InjectableFixture;

/**
 * Class Items
 * Adminhtml GiftMessage order view items block.
 */
class Items extends \Magento\Sales\Test\Block\Adminhtml\Order\View\Items
{
    /**
     * Item product selector.
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
            'Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items\ItemProduct',
            [
                'element' => $this->_rootElement->find(
                    sprintf($this->itemProduct, $product->getName()),
                    Locator::SELECTOR_XPATH
                )
            ]
        );
    }
}
