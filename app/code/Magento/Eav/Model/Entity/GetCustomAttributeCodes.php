<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;

use Magento\Framework\Api\MetadataServiceInterface;

class GetCustomAttributeCodes implements GetCustomAttributeCodesInterface
{
    /**
     * @var string[][]
     */
    private $customAttributesCodes;

    /**
     * Receive a list of custom EAV attributes using provided metadata service. The results are cached per entity type
     *
     * @param MetadataServiceInterface $metadataService Custom attribute metadata service to be used
     * @param int|null                 $attributeSetId  Optional attribute set ID, if provided will only load attributes
     *                                                  for that attribute set.
     *
     * @return string[]
     */
    public function execute(MetadataServiceInterface $metadataService, ?int $attributeSetId = null): array
    {
        $cacheKey = get_class($metadataService);
        if (!isset($this->customAttributesCodes[$cacheKey])) {
            $this->customAttributesCodes[$cacheKey] = $this->getEavAttributesCodes($metadataService);
        }
        return $this->customAttributesCodes[$cacheKey];
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
