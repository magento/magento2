<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Model\Quote;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\ToOrder as QuoteAddressToOrder;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\TaxDetails\AppliedTaxRateFactory;

class ToOrderConverter
{
    /**
     * @var QuoteAddress
     */
    protected $quoteAddress;

    /**
     * @var OrderExtensionFactory
     */
    protected $orderExtensionFactory;
    
    /**
     * @var OrderTaxDetailsAppliedTaxInterfaceFactory
     */
    private $orderTaxDetailsAppliedTaxInterfaceFactory;
    
    /**
     * @var OrderTaxDetailsItemInterfaceFactory
     */
    private $orderTaxDetailsItemInterfaceFactory;

    /**
     * @var OrderTaxDetailsAppliedTaxExtensionInterface
     */
    private $orderTaxDetailsAppliedTaxExtensionFactory;

    /**
     * @var AppliedTaxRateFactory
     */
    private $appliedTaxRateInterfaceFactory;

    /**
     * @var OrderTaxDetailsItemExtensionFactory
     */
    private $orderTaxDetailsItemExtensionFactory;

    /**
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderTaxDetailsAppliedTaxInterfaceFactory $orderTaxDetailsAppliedTaxInterfaceFactory
     * @param OrderTaxDetailsAppliedTaxExtensionFactory $orderTaxDetailsAppliedTaxExtensionFactory
     * @param OrderTaxDetailsItemInterfaceFactory $orderTaxDetailsItemInterfaceFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateInterfaceFactory
     * @param OrderTaxDetailsItemExtensionFactory $orderTaxDetailsItemExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        OrderTaxDetailsAppliedTaxInterfaceFactory $orderTaxDetailsAppliedTaxInterfaceFactory = null,
        OrderTaxDetailsAppliedTaxExtensionFactory $orderTaxDetailsAppliedTaxExtensionFactory = null,
        OrderTaxDetailsItemInterfaceFactory $orderTaxDetailsItemInterfaceFactory = null,
        AppliedTaxRateInterfaceFactory $appliedTaxRateInterfaceFactory = null,
        OrderTaxDetailsItemExtensionFactory $orderTaxDetailsItemExtensionFactory = null
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderTaxDetailsAppliedTaxInterfaceFactory = $orderTaxDetailsAppliedTaxInterfaceFactory
            ?: ObjectManager::getInstance(OrderTaxDetailsAppliedTaxInterfaceFactory::class);
        $this->orderTaxDetailsItemInterfaceFactory = $orderTaxDetailsItemInterfaceFactory ?:
            ObjectManager::getInstance(OrderTaxDetailsAppliedTaxExtensionFactory::class);
        $this->orderTaxDetailsAppliedTaxExtensionFactory = $orderTaxDetailsAppliedTaxExtensionFactory
            ?: ObjectManager::getInstance(OrderTaxDetailsItemInterfaceFactory::class);
        $this->appliedTaxRateInterfaceFactory = $appliedTaxRateInterfaceFactory
            ?: ObjectManager::getInstance(AppliedTaxRateInterfaceFactory::class);
        $this->orderTaxDetailsItemExtensionFactory = $orderTaxDetailsItemExtensionFactory
            ?: ObjectManager::getInstance(OrderTaxDetailsItemExtensionFactory::class);

    }

    /**
     * @param QuoteAddressToOrder $subject
     * @param QuoteAddress $address
     * @param array $additional
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeConvert(QuoteAddressToOrder $subject, QuoteAddress $address, $additional = []): array
    {
        $this->quoteAddress = $address;
        return [$address, $additional];
    }

    /**
     * @param QuoteAddressToOrder $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(QuoteAddressToOrder $subject, OrderInterface $order): OrderInterface
    {
        /** @var \Magento\Sales\Model\Order $order */
        $taxes = $this->quoteAddress->getAppliedTaxes();
        $orderExtensionAttributes = $order->getExtensionAttributes();
        
        if ($orderExtensionAttributes == null) {
            $orderExtensionAttributes = $this->orderExtensionFactory->create();
        }
        
        if (!empty($taxes)) {
            $orderTaxDetailsApplied = $this->getOrderAppliedTaxes($taxes);
            $orderExtensionAttributes->setAppliedTaxes($orderTaxDetailsApplied);
            $orderExtensionAttributes->setConvertingFromQuote(true);
        }

        /** @var array|null $itemAppliedTaxes */
        $itemAppliedTaxes = $this->quoteAddress->getItemsAppliedTaxes();
        if (!empty($itemAppliedTaxes)) {
            $itemAppliedTaxes = $this->getItemAppliedTaxes($itemAppliedTaxes);
            $orderExtensionAttributes->setItemAppliedTaxes($itemAppliedTaxes);
        }

        $order->setExtensionAttributes($orderExtensionAttributes);

        return $order;
    }

    /**
     * @param array $taxes
     * @return OrderTaxDetailsAppliedTaxInterface[]
     */
    private function getOrderAppliedTaxes(array $taxes): array
    {
        $orderAppliedTaxDetails = [];

        foreach ($taxes as $key => $tax) {
            /** @var OrderTaxDetailsAppliedTaxInterface $orderTaxDetailsApplied */
            $orderTaxDetailsApplied = $this->orderTaxDetailsAppliedTaxInterfaceFactory->create();

            $orderTaxDetailsApplied->setAmount($tax['amount']);
            $orderTaxDetailsApplied->setBaseAmount($tax['base_amount']);
            $orderTaxDetailsApplied->setCode($tax['id']);
            $orderTaxDetailsApplied->setPercent($tax['percent']);
            $orderTaxDetailsApplied->setTitle($tax['id']);

            $orderTaxDetailsAppliedExtensionAttributes = $orderTaxDetailsApplied->getExtensionAttributes();
            if (!$orderTaxDetailsAppliedExtensionAttributes) {
                /** @var OrderTaxDetailsAppliedTaxExtensionInterface $orderTaxDetailsAppliedExtensionAttributes */
                $orderTaxDetailsAppliedExtensionAttributes = $this->orderTaxDetailsAppliedTaxExtensionFactory->create();
            }

            /** @var AppliedTaxRateInterface[] $taxRates */
            $taxRates = [];
            if (isset($tax['rates'])) {
                foreach ($tax['rates'] as $taxRate) {
                    /** @var AppliedTaxRateInterface $taxRateData */
                    $taxRateData = $this->appliedTaxRateInterfaceFactory->create();
                    $taxRateData->setCode($taxRate['code']);
                    $taxRateData->setTitle($taxRate['title']);
                    $taxRateData->setPercent($taxRate['percent']);

                    $taxRates[] = $taxRateData;
                }
            }

            $orderTaxDetailsAppliedExtensionAttributes->setRates($taxRates);
            $orderTaxDetailsApplied->setExtensionAttributes($orderTaxDetailsAppliedExtensionAttributes);

            $orderAppliedTaxDetails[] = $orderTaxDetailsApplied;
        }

        return $orderAppliedTaxDetails;
    }

    /**
     * @param array $taxes
     *
     * @return OrderTaxDetailsItemInterface[]
     */
    private function getItemAppliedTaxes(array $taxes): array
    {
        $itemAppliedTaxes = [];

        foreach ($taxes as $key => $itemAppliedTaxItem) {
            if (is_array($itemAppliedTaxItem) && !empty($itemAppliedTaxItem)) {
                foreach ($itemAppliedTaxItem as $itemAppliedTax) {
                    /** @var OrderTaxDetailsItemInterface $itemAppliedTaxData */
                    $itemAppliedTaxData = $this->orderTaxDetailsItemInterfaceFactory->create();
                    $appliedTaxes = $this->getOrderAppliedTaxes($itemAppliedTaxItem);

                    $itemAppliedTaxData->setAssociatedItemId($itemAppliedTax['associated_item_id']);
                    $itemAppliedTaxData->setItemId($itemAppliedTax['item_id']);
                    $itemAppliedTaxData->setType($itemAppliedTax['item_type']);
                    $itemAppliedTaxData->setAppliedTaxes($appliedTaxes);

                    $itemAppliedTaxes[] = $itemAppliedTaxData;
                }
            }
        }

        return $itemAppliedTaxes;
    }
}
