<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Amount;

/**
 * Class AmountFactory
 *
 * @api
 * @since 100.0.2
 */
class AmountFactory
{
    /**
     * Default amount class
     */
    const DEFAULT_PRICE_AMOUNT_CLASS = \Magento\Framework\Pricing\Amount\AmountInterface::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Amount object
     *
     * @param float $amount
     * @param array $adjustmentAmounts
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @throws \InvalidArgumentException
     */
    public function create($amount, array $adjustmentAmounts = [])
    {
        $amountModel = $this->objectManager->create(
            self::DEFAULT_PRICE_AMOUNT_CLASS,
            [
                'amount' => $amount,
                'adjustmentAmounts' => $adjustmentAmounts
            ]
        );

        if (!$amountModel instanceof \Magento\Framework\Pricing\Amount\AmountInterface) {
            throw new \InvalidArgumentException(
                get_class($amountModel) . ' doesn\'t implement \Magento\Framework\Pricing\Amount\AmountInterface'
            );
        }

        return $amountModel;
    }
}
