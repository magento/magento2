<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Paypal\Block\Payflow\Bml;

use Magento\Paypal\Model\Config;

/**
 * @todo methodCode should be set in constructor, than this form should be eliminated
 *
 */
class Form extends \Magento\Paypal\Block\Bml\Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Config::METHOD_WPP_PE_BML;
}
