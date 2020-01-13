<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\AuthorizenetCardinal\Model\Config;

/**
 * Adds the payment info to the payment object
 *
 * @deprecated 100.3.1 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Cardinal JWT key
     */
    private const JWT_KEY = 'cardinalJWT';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isActive() === false) {
            return;
        }

        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);
        if (isset($additionalData[self::JWT_KEY])) {
            $paymentInfo->setAdditionalInformation(
                self::JWT_KEY,
                $additionalData[self::JWT_KEY]
            );
        }
    }
}
