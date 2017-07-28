<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class ConfigurableInfo
 * @api
 * @since 2.0.0
 */
class ConfigurableInfo extends \Magento\Payment\Block\Info
{
    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    private $config;

    /**
     * @param Context $context
     * @param ConfigInterface $config
     * @param array $data
     * @since 2.0.0
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

        if (isset($data['methodCode'])) {
            $this->config->setMethodCode($data['methodCode']);
        }
    }

    /**
     * Prepare payment information
     *
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $storedFields = explode(',', (string)$this->config->getValue('paymentInfoKeys'));
        if ($this->getIsSecureMode()) {
            $storedFields = array_diff(
                $storedFields,
                explode(',', (string)$this->config->getValue('privateInfoKeys'))
            );
        }

        foreach ($storedFields as $field) {
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
     * @param \Magento\Framework\DataObject $transport
     * @param string $field
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    protected function setDataToTransfer(
        \Magento\Framework\DataObject $transport,
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getValueView($field, $value)
    {
        return $value;
    }
}
