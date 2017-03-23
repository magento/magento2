<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Payflow\Bml;

use Magento\Paypal\Model\Config;

/** @todo methodCode should be set in constructor, than this form should be eliminated */
class Form extends \Magento\Paypal\Block\Bml\Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Config::METHOD_WPP_PE_BML;
}
