<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Get allowed address attributes
 */
class GetAllowedAddressAttributes
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
            AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS
        );
        foreach ($attributes as $attributeCode => $attribute) {
            if (false === $attribute->getIsVisibleOnFront()) {
                unset($attributes[$attributeCode]);
            }
        }
        return $attributes;
    }
}
