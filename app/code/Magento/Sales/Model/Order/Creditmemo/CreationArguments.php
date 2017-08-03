<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;

/**
 * Class CreationArguments
 * @since 2.1.3
 */
class CreationArguments implements CreditmemoCreationArgumentsInterface
{
    /**
     * @var float|null
     * @since 2.1.3
     */
    private $shippingAmount;

    /**
     * @var float|null
     * @since 2.1.3
     */
    private $adjustmentPositive;

    /**
     * @var float|null
     * @since 2.1.3
     */
    private $adjustmentNegative;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface
     * @since 2.1.3
     */
    private $extensionAttributes;

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function getAdjustmentPositive()
    {
        return $this->adjustmentPositive;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function getAdjustmentNegative()
    {
        return $this->adjustmentNegative;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function setShippingAmount($amount)
    {
        $this->shippingAmount = $amount;
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function setAdjustmentPositive($amount)
    {
        $this->adjustmentPositive = $amount;
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function setAdjustmentNegative($amount)
    {
        $this->adjustmentNegative = $amount;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;

        return $this;
    }
}
