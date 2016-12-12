<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\Sales\Model\OrderFactory;

/**
 * Handles the conversion from Magento Order to Signifyd Case.
 */
class CreateCaseBuilder implements CreateCaseBuilderInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PurchaseBuilder
     */
    private $purchaseBuilder;

    /**
     * @var CardBuilder
     */
    private $cardBuilder;

    /**
     * @var RecipientBuilder
     */
    private $recipientBuilder;

    /**
     * @param OrderFactory $orderFactory
     * @param PurchaseBuilder $purchaseBuilder
     * @param CardBuilder $cardBuilder
     * @param RecipientBuilder $recipientBuilder
     */
    public function __construct(
        OrderFactory $orderFactory,
        PurchaseBuilder $purchaseBuilder,
        CardBuilder $cardBuilder,
        RecipientBuilder $recipientBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->purchaseBuilder = $purchaseBuilder;
        $this->cardBuilder = $cardBuilder;
        $this->recipientBuilder = $recipientBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build($orderId)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create()->load($orderId);

        return array_merge(
            $this->purchaseBuilder->build($order),
            $this->cardBuilder->build($order),
            $this->recipientBuilder->build($order)
        );
    }
}
