<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\EavGraphQl\Model\Resolver\Query\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * Resolve data for custom attribute metadata requests
 */
class CustomAttributeMetadata implements ResolverInterface
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @param Type $type
     * @param Attribute $attribute
     */
    public function __construct(Type $type, Attribute $attribute)
    {
        $this->type = $type;
        $this->attribute = $attribute;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $attributes['items'] = null;
        $attributeInputs = $args['attributes'];
        foreach ($attributeInputs as $attributeInput) {
            if (!isset($attributeInput['attribute_code']) || !isset($attributeInput['entity_type'])) {
                $attributes['items'][] = $this->createInputException($attributeInput);
                continue;
            }
            try {
                $attribute = $this->attribute->getAttribute(
                    $attributeInput['attribute_code'],
                    $attributeInput['entity_type']
                );
                $type = $this->type->getType($attributeInput['attribute_code'], $attributeInput['entity_type']);
            } catch (InputException $exception) {
                $attributes['items'][] = new GraphQlNoSuchEntityException(
                    __(
                        'Attribute code %1 of entity type %2 not configured to have a type.',
                        [$attributeInput['attribute_code'], $attributeInput['entity_type']]
                    )
                );
                continue;
            } catch (LocalizedException $exception) {
                $attributes['items'][] = new GraphQlInputException(
                    __(
                        'Invalid entity_type specified: %1',
                        [$attributeInput['entity_type']]
                    )
                );
                continue;
            }

            if (empty($type)) {
                continue;
            }

            $attributes['items'][] = [
                'attribute_code' => $attributeInput['attribute_code'],
                'entity_type' => $attributeInput['entity_type'],
                'attribute_type' => ucfirst($type),
                'input_type' => isset($attribute) ? $attribute->getFrontendInput() : null,
                'storefront_properties' => isset($attribute) ?$this->getStorefrontProperties($attribute) : null
            ];
        }

        return $attributes;
    }

    /**
     * Format storefront properties
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function getStorefrontProperties(AttributeInterface $attribute)
    {
        return [
            'position'=> $attribute->getPosition(),
            'visible_on_catalog_pages'=> $attribute->getIsVisibleOnFront(),
            'use_in_search_results_layered_navigation' => $attribute->getIsFilterableInSearch(),
            'use_in_product_listing'=> $attribute->getUsedInProductListing(),
            'use_in_layered_navigation'=>
                $this->getLayeredNavigationPropertiesEnum()[$attribute->getisFilterable()] ?? null
        ];
    }

    /**
     * Return enum for resolving use in layered navigation
     *
     * @return string[]
     */
    private function getLayeredNavigationPropertiesEnum() {
        return [
            0 => 'NO',
            1 => 'FILTERABLE_WITH_RESULTS',
            2 => 'FILTERABLE_NO_RESULT'
        ];
    }

    /**
     * Create GraphQL input exception for an invalid attribute input
     *
     * @param array $attribute
     * @return GraphQlInputException
     */
    private function createInputException(array $attribute) : GraphQlInputException
    {
        $isCodeSet = isset($attribute['attribute_code']);
        $isEntitySet = isset($attribute['entity_type']);
        $messagePart = !$isCodeSet ? 'attribute_code' : 'entity_type';
        $messagePart .= !$isCodeSet && !$isEntitySet ? '/entity_type' : '';
        $identifier = "Empty AttributeInput";
        if ($isCodeSet) {
            $identifier = 'attribute_code: ' . $attribute['attribute_code'];
        } elseif ($isEntitySet) {
            $identifier = 'entity_type: ' . $attribute['entity_type'];
        }

        return new GraphQlInputException(
            __(
                'Missing %1 for the input %2.',
                [$messagePart, $identifier]
            )
        );
    }
}
