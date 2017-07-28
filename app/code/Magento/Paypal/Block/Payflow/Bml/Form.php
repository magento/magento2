<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Payflow\Bml;

use Magento\Paypal\Model\Config;

/**
 * @todo methodCode should be set in constructor, than this form should be eliminated
 *
 * @since 2.0.0
 */
class Form extends \Magento\Paypal\Block\Bml\Form
{
    /**
     * Payment method code
     * @var string
     * @since 2.0.0
     */
    protected $_methodCode = Config::METHOD_WPP_PE_BML;
}
