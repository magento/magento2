<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Pricing\Price;

use Magento\Downloadable\Model\Link;

/**
 * Class LinkPrice Model
 * @since 2.0.0
 */
interface LinkPriceInterface
{
    /**
     * @param Link $link
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    public function getLinkAmount(Link $link);
}
