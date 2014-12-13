<?php
/**
 * @spi
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Theme\Test\Block\Html;

use Mtf\Block\Block;

/**
 * Page title block
 */
class Title extends Block
{
    /**
     * Get title of current page
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_rootElement->getText();
    }
}
