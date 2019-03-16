<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerMetadataManagementInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Get allowed address attributes
 */
class GetAllowedCustomerAttributes
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param Config $eavConfig
     */
    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get allowed address attributes
     *
     * @return AbstractAttribute[]
     */
    public function execute(): array
    {
        $attributes = $this->eavConfig->getEntityAttributes(
            CustomerMetadataManagementInterface::ENTITY_TYPE_CUSTOMER
        );
        foreach ($attributes as $attributeCode => $attribute) {
            if (false === $attribute->getIsVisibleOnFront()) {
                unset($attributes[$attributeCode]);
            }
        }
        return $attributes;
    }
}
