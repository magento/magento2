<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\View;

use Mtf\Client\Element\Locator;
use Mtf\Fixture\InjectableFixture;
use Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items\ItemProduct;

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
