<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Selector
 * URL rewrite entity type selector
 */
class Selector extends Block
{
    /**
     * Select URL type
     *
     * @param string $urlrewriteType
     * @return void
     */
    public function selectType($urlrewriteType)
    {
        $this->_rootElement->find("[data-role=entity-type-selector]", Locator::SELECTOR_CSS, 'select')
            ->setValue($urlrewriteType);
    }
}
