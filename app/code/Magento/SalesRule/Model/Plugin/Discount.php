<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\Data\Collection;
use Magento\SalesRule\Api\Data\DiscountInterfaceFactory;

/**
 * Plugin for persisting discounts along with Quote Address
 */
class Discount
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     */
    private $discountFactory;

    /**
     * @var DiscountInterfaceFactory
     */
    private $discountInterfaceFactory;

    /**
     * @param Json $json
     * @param DataFactory $discountDataFactory
     * @param DiscountInterfaceFactory $discountInterfaceFactory
     */
    public function __construct(
        Json $json,
        DataFactory $discountDataFactory,
        DiscountInterfaceFactory $discountInterfaceFactory
    ) {
        $this->json = $json;
        $this->discountFactory = $discountDataFactory;
        $this->discountInterfaceFactory = $discountInterfaceFactory;
    }

    /**
     * Plugin for adding item discounts to extension attributes
     *
     * @param Quote $subject
     * @param Collection $result
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemsCollection(
        Quote $subject,
        Collection $result
    ) {
        foreach ($result as $item) {
            if ($item->getDiscounts() && !$item->getExtensionAttributes()->getDiscounts()) {
                $unserializeDiscounts = $this->json->unserialize($item->getDiscounts());
                $discounts = [];
                foreach ($unserializeDiscounts as $value) {
                    $itemDiscount = $this->discountInterfaceFactory->create();
                    $itemDiscount->setDiscountData($this->unserializeDiscountData($value['discount']));
                    $itemDiscount->setRuleLabel($value['rule']);
                    $itemDiscount->setRuleID($value['ruleID']);
                    $discounts[] = $itemDiscount;
                }
                $itemExtension = $item->getExtensionAttributes();
                $itemExtension->setDiscounts($discounts);
            }
        }
        return $result;
    }

    /**
     * Plugin for adding address level discounts to extension attributes
     *
     * @param Quote $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAddresses(
        Quote $subject,
        array $result
    ) {
        foreach ($result as $address) {
            if ($address->getDiscounts() && !$address->getExtensionAttributes()->getDiscounts()) {
                $unserializedDiscounts = $this->json->unserialize($address->getDiscounts());
                $discounts = [];
                foreach ($unserializedDiscounts as $value) {
                    $cartDiscount = $this->discountInterfaceFactory->create();
                    $cartDiscount->setDiscountData($this->unserializeDiscountData($value['discount']));
                    $cartDiscount->setRuleLabel($value['rule']);
                    $cartDiscount->setRuleID($value['ruleID']);
                    $discounts[] = $cartDiscount;
                }
                $addressExtension = $address->getExtensionAttributes();
                $addressExtension->setDiscounts($discounts);
            }
        }
        return $result;
    }

    /**
     * Unserialize discount object
     *
     * @param string $serializedDiscount
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    private function unserializeDiscountData(string $serializedDiscount)
    {
        $discountArray = $this->json->unserialize($serializedDiscount);
        $discountData = $this->discountFactory->create();
        $discountData->setBaseOriginalAmount($discountArray['baseOriginalAmount']);
        $discountData->setOriginalAmount($discountArray['originalAmount']);
        $discountData->setAmount($discountArray['amount']);
        $discountData->setBaseAmount($discountArray['baseAmount']);
        return $discountData;
    }
}
