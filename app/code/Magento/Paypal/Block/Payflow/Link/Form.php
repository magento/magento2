<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payflow\Link;

/**
 * Payflow link iframe block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'payflowlink/info.phtml';

    /**
     * Get frame action URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getFrameActionUrl()
    {
        return $this->getUrl('paypal/payflow/form', ['_secure' => true]);
    }
}
