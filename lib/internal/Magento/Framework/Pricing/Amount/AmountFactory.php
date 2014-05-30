<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Pricing\Amount;

/**
 * Class AmountFactory
 */
class AmountFactory
{
    /**
     * Default amount class
     */
    const DEFAULT_PRICE_AMOUNT_CLASS = 'Magento\Framework\Pricing\Amount\AmountInterface';

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
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
