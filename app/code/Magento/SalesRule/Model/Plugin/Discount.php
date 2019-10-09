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
     * @param Json $json
     * @param DataFactory|null $discountDataFactory
     */
    public function __construct(Json $json, DataFactory $discountDataFactory)
    {
        $this->json = $json;
        $this->discountFactory = $discountDataFactory;
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
                $discounts = $this->json->unserialize($item->getDiscounts());
                foreach ($discounts as $key => $value) {
                    $discounts[$key]['discount'] = $this->unserializeDiscountData($value['discount']);
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
                $discounts = $this->json->unserialize($address->getDiscounts());
                foreach ($discounts as $key => $value) {
                    $discounts[$key]['discount'] = $this->unserializeDiscountData($value['discount']);
                }
                $itemExtension = $address->getExtensionAttributes();
                $itemExtension->setDiscounts($discounts);
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
