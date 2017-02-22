<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Return credit cart type
     * 
     * @return string
     */
    protected function getCcTypeName()
    {
        $types = $this->paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        } else {
            return __('Stored Card');
        }
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param null | array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $info = $this->getInfo();
        if ($ccType = $this->getCcTypeName()) {
            $data[__('Credit Card Type')->getText()] = $ccType;
        }
        if ($info->getCcLast4()) {
            $data[__('Credit Card Number')->getText()] =
                sprintf('xxxx-%s', $info->getCcLast4());
        }
        if ($this->_appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
            && $info->getAdditionalInformation()
        ) {
            foreach ($info->getAdditionalInformation() as $field => $value) {
                $beautifiedFieldName = ucwords(trim(preg_replace('/(?<=\\w)(?=[A-Z])/', " $1", $field)));
                $data[__($beautifiedFieldName)->getText()] = $value;
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }

    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildHtml($name = '', $useCache = true)
    {
        $payment = $this->getRequest()->getPost('payment');
        $result = "";
        $deviceData = $this->getRequest()->getPost('device_data');

        if (isset($payment["cc_token"]) && $payment["cc_token"]) {
            $ccToken = $payment["cc_token"];
            $result .= "<input type='hidden' name='payment[cc_token]' value='$ccToken'>";
        }
        if (isset($payment['store_in_vault']) && $payment['store_in_vault']) {
            $storeInVault = $payment['store_in_vault'];
            $result .= "<input type='hidden' name='payment[store_in_vault]' value='$storeInVault'>";
        }
        if ($deviceData) {
            $result .= "<input type='hidden' name='device_data' value='$deviceData'>";
        }
        return $result;
    }
}
