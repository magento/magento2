<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;

class ConfigurableInfo extends \Magento\Payment\Block\Info
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Context $context
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;

        if (isset($data['pathPattern'])) {
            $this->config->setPathPattern($data['pathPattern']);
        }

        if (isset($data['pathPattern'])) {
            $this->config->setMethodCode($data['methodCode']);
        }
    }

    /**
     * Prepare PayPal-specific payment information
     *
     * @param \Magento\Framework\Object|array|null $transport
     * @return \Magento\Framework\Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $fieldsToStore = explode(',', (string)$this->config->getValue('paymentInfoKeys'));
        if ($this->getIsSecureMode()) {
            $fieldsToStore = array_diff(
                $fieldsToStore,
                explode(',', (string)$this->config->getValue('privateInfoKeys'))
            );
        }

        foreach ($fieldsToStore as $field) {
            if ($payment->getAdditionalInformation($field) !== null) {
                $this->setDataToTransfer(
                    $transport,
                    $field,
                    $payment->getAdditionalInformation($field)
                );

            }
        }

        return $transport;
    }

    /**
     * Sets data to transport
     *
     * @param \Magento\Framework\Object $transport
     * @param string $field
     * @param string $value
     * @return void
     */
    protected function setDataToTransfer(
        \Magento\Framework\Object $transport,
        $field,
        $value
    ) {
        $transport->setData(
            (string)$this->getLabel($field),
            (string)$this->getValueView(
                $field,
                $value
            )
        );
    }

    /**
     * Returns label
     *
     * @param string $field
     * @return string | Phrase
     */
    protected function getLabel($field)
    {
        return $field;
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string $value
     * @return string | Phrase
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getValueView($field, $value)
    {
        return $value;
    }
}
