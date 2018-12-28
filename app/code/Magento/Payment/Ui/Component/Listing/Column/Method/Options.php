<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Ui\Component\Listing\Column\Method;

use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->paymentMethodList = $paymentMethodList;
        $this->storeManager = $storeManager;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->getPaymentOptions();
//            $this->options = $this->paymentHelper->getPaymentMethodList(true, true);
        }
        return $this->options;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPaymentOptions()
    {
        $options = [];
        foreach ($this->paymentMethodList->getList($this->storeManager->getStore()->getId()) as $option) {
            $options[$option->getCode()] = ['value' => $option->getCode(), 'label' => $option->getTitle()];
        }
        asort($options);
        return $options;
    }
}
