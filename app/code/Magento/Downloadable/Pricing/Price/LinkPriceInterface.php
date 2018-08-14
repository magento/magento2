<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Pricing\Price;

use Magento\Downloadable\Model\Link;

/**
 * Class LinkPrice Model
 */
interface LinkPriceInterface
{
    /**
     * @param Link $link
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getLinkAmount(Link $link);
}
