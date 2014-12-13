<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
