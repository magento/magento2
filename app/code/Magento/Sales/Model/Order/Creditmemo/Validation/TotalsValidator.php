<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Validation;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class TotalsValidator
 */
class TotalsValidator implements ValidatorInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * TotalsValidator constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritDoc
     */
    public function validate($entity)
    {
        $messages = [];
        $baseOrderRefund = $this->priceCurrency->round(
            $entity->getOrder()->getBaseTotalRefunded() + $entity->getBaseGrandTotal()
        );
        if ($baseOrderRefund > $this->priceCurrency->round($entity->getOrder()->getBaseTotalPaid())) {
            $baseAvailableRefund = $entity->getOrder()->getBaseTotalPaid()
                - $entity->getOrder()->getBaseTotalRefunded();

            $messages[] = __(
                'The most money available to refund is %1.',
                $baseAvailableRefund
            );
        }

        return $messages;
    }
}
