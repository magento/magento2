<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Ui\Component\Listing\Column\Method;

/**
 * Class Options
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->paymentHelper->getPaymentMethodList(true, true);
        }
        return $this->options;
    }
}
