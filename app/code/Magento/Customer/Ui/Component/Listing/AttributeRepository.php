<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\AddressMetadataInterface;

class AttributeRepository
{
    const BILLING_ADDRESS_PREFIX = 'billing_';

    /**
     * @var null|\Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected $attributes;

    /**
     * @var \Magento\Customer\Model\Resource\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Customer\Model\Resource\Address\Attribute\Collection
     */
    protected $addressAttributeCollection;

    /**
     * @var \Magento\Customer\Model\Metadata\CustomerMetadata
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Customer\Model\Metadata\AddressMetadata
     */
    protected $addressMetadata;

    /**
     * @param \Magento\Customer\Model\Resource\Attribute\Collection $attributeCollection
     * @param \Magento\Customer\Model\Resource\Address\Attribute\Collection $addressAttributeCollection
     * @param \Magento\Customer\Model\Metadata\CustomerMetadata $customerMetadata
     * @param \Magento\Customer\Model\Metadata\AddressMetadata $addressMetadata
     */
    public function __construct(
        \Magento\Customer\Model\Resource\Attribute\Collection $attributeCollection,
        \Magento\Customer\Model\Resource\Address\Attribute\Collection $addressAttributeCollection,
        \Magento\Customer\Model\Metadata\CustomerMetadata $customerMetadata,
        \Magento\Customer\Model\Metadata\AddressMetadata $addressMetadata
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->addressAttributeCollection = $addressAttributeCollection;
        $this->customerMetadata = $customerMetadata;
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * @return AttributeMetadataInterface[]
     */
    public function getList()
    {
        if (null == $this->attributes) {
            $this->attributes = [];
            $attributes = array_merge(
                $this->attributeCollection->getItems(),
                $this->addressAttributeCollection->getItems()
            );

            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                $entityTypeCode = $attribute->getEntityType()->getEntityTypeCode();
                $attribute = $this->getAttributeMetadata($attribute);
                $attributeCode = $attribute->getAttributeCode();

                if ($entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
                    $attributeCode = self::BILLING_ADDRESS_PREFIX . $attribute->getAttributeCode();
                }
                $this->attributes[$attributeCode] = $attribute;
            }
        }
        return $this->attributes;
    }

    /**
     * @param string $code
     * @return AttributeMetadataInterface|null
     */
    public function getMetadataByCode($code)
    {
        return isset($this->getList()[$code]) ? $this->getList()[$code] : null;
    }

    /**
     * @param Attribute $attribute
     * @return AttributeMetadataInterface|null
     */
    protected function getAttributeMetadata(Attribute $attribute)
    {
        $metadata = null;
        if ($attribute->getEntityType()->getEntityTypeCode() == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $metadata = $this->addressMetadata->getAttributeMetadata($attribute->getAttributeCode());
        } else {
            $metadata = $this->customerMetadata->getAttributeMetadata($attribute->getAttributeCode());
        }
        return $metadata;
    }
}
