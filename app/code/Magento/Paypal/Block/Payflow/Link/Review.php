<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Paypal PayflowLink Express Onepage checkout block
 *
 * @deprecated since 1.6.2.0
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Paypal\Block\Payflow\Link;

class Review extends \Magento\Paypal\Block\Express\Review
{
    /**
     * Retrieve payment method and assign additional template values
     *
     * @return \Magento\Paypal\Block\Express\Review
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
