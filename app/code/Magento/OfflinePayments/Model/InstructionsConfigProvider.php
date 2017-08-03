<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class \Magento\OfflinePayments\Model\InstructionsConfigProvider
 *
 * @since 2.0.0
 */
class InstructionsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $methodCodes = [
        Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
        Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     * @since 2.0.0
     */
    protected $methods = [];

    /**
     * @var Escaper
     * @since 2.0.0
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @since 2.0.0
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     * @since 2.0.0
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }
}
