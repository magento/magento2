<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payflow\Link;

/**
 * Payflow link iframe block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     */
    protected $_template = 'payflowlink/info.phtml';

    /**
     * Get frame action URL
     *
     * @return string
     */
    public function getFrameActionUrl()
    {
        return $this->getUrl('paypal/payflow/form', ['_secure' => true]);
    }
}
