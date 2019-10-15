<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Plugin\ResourceModel;

use Magento\Framework\Serialize\Serializer\Json;

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
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * Plugin method for persisting data from extension attribute
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Quote\Model\ResourceModel\Quote $subject,
        \Magento\Framework\Model\AbstractModel $object
    ): array {
        foreach ($object->getAllAddresses() as $address) {
            $discounts = $address->getExtensionAttributes()->getDiscounts();
            $serializedDiscounts=  [];
            if ($discounts) {
                foreach ($discounts as $key => $value) {
                    $discount = $value->getDiscountData();
                    $discountData = [
                        "amount" => $discount->getAmount(),
                        "baseAmount" => $discount->getBaseAmount(),
                        "originalAmount" => $discount->getOriginalAmount(),
                        "baseOriginalAmount" => $discount->getBaseOriginalAmount()
                    ];
                    $serializedDiscounts[$key]['discount'] = $this->json->serialize($discountData);
                    $serializedDiscounts[$key]['rule'] = $value->getRuleLabel();
                    $serializedDiscounts[$key]['ruleID'] = $value->getRuleID();
                }
                $address->setDiscounts($this->json->serialize($serializedDiscounts));
            }
        }
        return [$object];
    }
}
