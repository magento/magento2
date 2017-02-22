<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Info;

class PayPal extends \Magento\Payment\Block\Info
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
     * Return credit card type
     * 
     * @return string
     */
    protected function getCcTypeName()
    {
        return null;
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
        $result = "";
        $deviceData = $this->getRequest()->getPost('device_data');

        if ($deviceData) {
            $result .= "<input type='hidden' name='device_data' value='$deviceData'>";
        }
        return $result;
    }
}
