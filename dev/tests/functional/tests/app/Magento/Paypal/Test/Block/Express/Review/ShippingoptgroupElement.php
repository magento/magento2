<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Express\Review;

use Magento\Mtf\Client\Element\OptgroupselectElement;

/**
 * Typified element class for select with shipping methods.
 */
class ShippingoptgroupElement extends OptgroupselectElement
{
    /**
     * Option group locator.
     *
     * @var string
     */
    protected $optGroupValue = ".//optgroup[@label = '%s']/option[contains(text(), '%s')]";
}
