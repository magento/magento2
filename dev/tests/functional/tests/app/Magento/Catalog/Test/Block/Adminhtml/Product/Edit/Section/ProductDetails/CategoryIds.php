<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\MultisuggestElement;

/**
 * Typified element class for category element.
 */
class CategoryIds extends MultisuggestElement
{
    /**
     * Selector item of search result.
     *
     * @var string
     */
    protected $resultItem = './/label[contains(@class, "admin__action-multiselect-label")]/span[text() = "%s"]';
}
