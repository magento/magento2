<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payflow\Advanced;

/**
 * Payflow Advanced iframe block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Paypal\Block\Payflow\Link\Form
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'payflowadvanced/info.phtml';

    /**
     * Get frame action URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getFrameActionUrl()
    {
        return $this->getUrl('paypal/payflowadvanced/form', ['_secure' => true]);
    }
}
