<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Ui\Component\Listing\Column\Method;

/**
 * Class Options
 * @since 2.0.0
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @since 2.0.0
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->paymentHelper->getPaymentMethodList(true, true);
        }
        return $this->options;
    }
}
