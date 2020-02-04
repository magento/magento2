<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Model\OrderFactory;

/**
 * Signifyd case creation request builder.
 *
 * Handles the conversion from Magento Order to Signifyd Case.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var SellerBuilder
     */
    private $sellerBuilder;

    /**
     * @var ClientVersionBuilder
     */
    private $clientVersionBuilder;

    /**
     * @var UserAccountBuilder
     */
    private $userAccountBuilder;

    /**
     * @param OrderFactory $orderFactory
     * @param PurchaseBuilder $purchaseBuilder
     * @param CardBuilder $cardBuilder
     * @param RecipientBuilder $recipientBuilder
     * @param SellerBuilder $sellerBuilder
     * @param ClientVersionBuilder $clientVersionBuilder
     * @param UserAccountBuilder $userAccountBuilder
     */
    public function __construct(
        OrderFactory $orderFactory,
        PurchaseBuilder $purchaseBuilder,
        CardBuilder $cardBuilder,
        RecipientBuilder $recipientBuilder,
        SellerBuilder $sellerBuilder,
        ClientVersionBuilder $clientVersionBuilder,
        UserAccountBuilder $userAccountBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->purchaseBuilder = $purchaseBuilder;
        $this->cardBuilder = $cardBuilder;
        $this->recipientBuilder = $recipientBuilder;
        $this->sellerBuilder = $sellerBuilder;
        $this->clientVersionBuilder = $clientVersionBuilder;
        $this->userAccountBuilder = $userAccountBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build($orderId)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create()->load($orderId);

        return $this->removeEmptyValues(
            array_merge(
                $this->purchaseBuilder->build($order),
                $this->cardBuilder->build($order),
                $this->recipientBuilder->build($order),
                $this->userAccountBuilder->build($order),
                $this->sellerBuilder->build($order),
                $this->clientVersionBuilder->build()
            )
        );
    }

    /**
     * Remove empty and null values.
     *
     * @param array $data
     * @return array
     */
    private function removeEmptyValues($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->removeEmptyValues($data[$key]);
            }

            if ($this->isEmpty($data[$key])) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Empty values are null, empty string and empty array.
     *
     * @param mixed $value
     * @return bool
     */
    private function isEmpty($value)
    {
        return $value === null || (is_array($value) && empty($value));
    }
}
