<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Page;

use Magento\Mtf\Block\Block;

/**
 * 404 error backend block.
 */
class Error extends Block
{
    /**
     * Get block text content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_rootElement->getText();
    }
}
