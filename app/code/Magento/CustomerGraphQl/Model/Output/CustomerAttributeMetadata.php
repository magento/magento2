<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Output;

use Magento\Customer\Api\MetadataInterface;
use Magento\Customer\Model\Data\ValidationRule;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Output\GetAttributeDataInterface;
use Magento\EavGraphQl\Model\Uid as AttributeUid;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Format attributes metadata for GraphQL output
 */
class CustomerAttributeMetadata implements GetAttributeDataInterface
{
    /**
     * @var AttributeUid
     */
    private AttributeUid $attributeUid;

    /**
     * @var Uid
     */
    private Uid $uid;

    /**
     * @var EnumLookup
     */
    private EnumLookup $enumLookup;

    /**
     * @var MetadataInterface
     */
    private MetadataInterface $metadata;

    /**
     * @var string
     */
    private string $entityType;

    /**
     * @param AttributeUid $attributeUid
     * @param Uid $uid
     * @param EnumLookup $enumLookup
     * @param MetadataInterface $metadata
     * @param string $entityType
     */
    public function __construct(
        AttributeUid $attributeUid,
        Uid $uid,
        EnumLookup $enumLookup,
        MetadataInterface $metadata,
        string $entityType
    ) {
        $this->attributeUid = $attributeUid;
        $this->uid = $uid;
        $this->enumLookup = $enumLookup;
        $this->metadata = $metadata;
        $this->entityType = $entityType;
    }

    /**
     * Retrieve formatted attribute data
     *
     * @param AttributeInterface $attribute
     * @param string $entityType
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(
        AttributeInterface $attribute,
        string $entityType,
        int $storeId
    ): array {
        if ($entityType !== $this->entityType) {
            return [];
        }

        $attributeMetadata = $this->metadata->getAttributeMetadata($attribute->getAttributeCode());
        $data = [];

        $validationRules = array_map(function (ValidationRule $validationRule) {
            return [
                'name' => $this->enumLookup->getEnumValueFromField(
                    'ValidationRuleEnum',
                    strtoupper($validationRule->getName())
                ),
                'value' => $validationRule->getValue()
            ];
        }, $attributeMetadata->getValidationRules());

        if ($attributeMetadata->isVisible()) {
            $data = [
                'input_filter' =>
                    empty($attributeMetadata->getInputFilter())
                        ? 'NONE'
                        : $this->enumLookup->getEnumValueFromField(
                            'InputFilterEnum',
                            strtoupper($attributeMetadata->getInputFilter())
                        ),
                'multiline_count' => $attributeMetadata->getMultilineCount(),
                'sort_order' => $attributeMetadata->getSortOrder(),
                'validate_rules' => $validationRules,
                'attributeMetadata' => $attributeMetadata
            ];
        }

        return $data;
    }
}
