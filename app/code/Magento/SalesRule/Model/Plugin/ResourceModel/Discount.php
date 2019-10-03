<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     */
    public function beforeSave(
        \Magento\Quote\Model\ResourceModel\Quote $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        foreach ($object->getAllAddresses() as $address) {
            $discounts = $address->getExtensionAttributes()->getDiscounts();
            if ($discounts) {
                $address->setDiscounts($this->json->serialize($discounts));
            }
        }
        return [$object];
    }
}
