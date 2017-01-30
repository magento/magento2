<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $optionGroupValue = ".//optgroup[@label = '%s']/option[contains(text(), '%s')]";
}
