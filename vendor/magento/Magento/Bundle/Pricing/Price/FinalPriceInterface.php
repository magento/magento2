<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface FinalPriceInterface
 */
interface FinalPriceInterface extends \Magento\Catalog\Pricing\Price\FinalPriceInterface
{
    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getPriceWithoutOption();
}
