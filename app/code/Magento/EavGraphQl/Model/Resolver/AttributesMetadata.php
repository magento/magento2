<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\EavGraphQl\Model\GetAttributesMetadata;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Load EAV attributes by attribute_code and entity_type
 */
class AttributesMetadata implements ResolverInterface
{
    /**
     * @var GetAttributesMetadata
     */
    private GetAttributesMetadata $getAttributesMetadata;

    /**
     * @param GetAttributesMetadata $getAttributesMetadata
     */
    public function __construct(
        GetAttributesMetadata $getAttributesMetadata
    ) {
        $this->getAttributesMetadata = $getAttributesMetadata;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $attributeInputs = $args['attributes'];

        if (empty($attributeInputs)) {
            throw new GraphQlInputException(
                __(
                    'Required parameters "attribute_code" and "entity_type" of type String.'
                )
            );
        }

        foreach ($attributeInputs as $attributeInput) {
            if (!isset($attributeInput['attribute_code'])) {
                throw new GraphQlInputException(__('The attribute_code is required to retrieve the metadata'));
            }
            if (!isset($attributeInput['entity_type'])) {
                throw new GraphQlInputException(__('The entity_type is required to retrieve the metadata'));
            }
        }

        return $this->getAttributesMetadata->execute(
            $attributeInputs,
            (int) $context->getExtensionAttributes()->getStore()->getId()
        );
    }
}
