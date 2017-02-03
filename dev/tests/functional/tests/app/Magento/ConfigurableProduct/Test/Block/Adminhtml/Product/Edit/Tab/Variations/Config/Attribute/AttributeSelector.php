<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Attribute;

use Magento\Mtf\Client\Element\SuggestElement;

/**
 * Form Attribute Search on Product page.
 */
class AttributeSelector extends SuggestElement
{
    /**
     * Wait for search result is visible.
     *
     * @return void
     */
    public function waitResult()
    {
        try {
            $this->waitUntil(
                function () {
                    return $this->find($this->searchResult)->isVisible() ? true : null;
                }
            );
        } catch (\Exception $e) {
            // In parallel run on windows change the focus is lost on element
            // that causes disappearing of result suggest list.
        }
    }
}
