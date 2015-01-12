<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Denied
 * Access Denied Block
 *
 */
class Denied extends Block
{
    /**
     * Block with "Access Denied Text"
     *
     * @var string
     */
    protected $accessDeniedText = ".page-heading";

    /**
     * Get comments history
     *
     * @return string
     */
    public function getTextFromAccessDeniedBlock()
    {
        return $this->_rootElement->find($this->accessDeniedText, Locator::SELECTOR_CSS)->getText();
    }
}
