<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;


use Magento\Framework\Api\MetadataServiceInterface;

class GetCustomAttributeCodes
{
    /**
     * @var string[]
     */
    private $customAttributesCodes;

    /**
     * Receive a list of custom EAV attributes using provided metadata service. The results are cached per entity type
     *
     * @param MetadataServiceInterface $metadataService Custom attribute metadata service to be used
     * @param string[] $interfaceAttributes Attribute codes that are part of the interface and should not be
     *                                      considered custom
     * @param string|null $entityType Entity type (class name), only needed if metadata service handles different
     *                                entities
     * @return string[]
     */
    public function execute(
        MetadataServiceInterface $metadataService,
        array $interfaceAttributes,
        string $entityType = null
    ): array {
        $cacheKey = get_class($metadataService) . '|' . $entityType;
        if (!isset($this->customAttributesCodes[$cacheKey])) {
            $customAttributesCodes = $this->getEavAttributesCodes($metadataService, $entityType);
            $this->customAttributesCodes[$cacheKey] = array_values(
                array_diff($customAttributesCodes, $interfaceAttributes)
            );
        }
        return $this->customAttributesCodes;
    }

    /**
     * Receive a list of EAV attributes using provided metadata service.
     *
     * @param MetadataServiceInterface $metadataService
     * @param string|null $entityType
     * @return string[]
     */
    private function getEavAttributesCodes(MetadataServiceInterface $metadataService, string $entityType = null)
    {
        $attributeCodes = [];
        $customAttributesMetadata = $metadataService->getCustomAttributesMetadata($entityType);
        if (is_array($customAttributesMetadata)) {
            /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
            foreach ($customAttributesMetadata as $attribute) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }
        }
        return $attributeCodes;
    }
}
