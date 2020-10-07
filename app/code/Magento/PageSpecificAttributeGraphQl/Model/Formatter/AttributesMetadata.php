<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageSpecificAttributeGraphQl\Model\Formatter;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\RmaGraphQl\Model\IdEncoder;

/**
 * Attributes metadata formatter
 */
class AttributesMetadata
{
    /**
     * @var IdEncoder
     */
    private $idEncoder;

    /**
     * @param IdEncoder $idEncoder
     */
    public function __construct(IdEncoder $idEncoder)
    {
        $this->idEncoder = $idEncoder;
    }

    /**
     * Format Attributes metadata according to the GraphQL schema
     *
     * @param AttributeMetadataInterface $attribute
     * @return array
     */
    public function format(AttributeMetadataInterface $attribute): array
    {
        return [
            'uid' => $this->idEncoder->encode($attribute->getAttributeId()),
            'attribute_code' => $attribute->getAttributeCode(),
            'entity_type' => $attribute->getEntityTypeId(),
            'attribute_type' => ucfirst($attribute->getBackendType()),
            'input_type' => $attribute->getFrontendInput()
        ];
    }
}
