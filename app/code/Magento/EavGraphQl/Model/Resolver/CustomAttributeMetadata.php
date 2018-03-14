<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EavGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\ArgumentInterface;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;

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
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ) : ?array {
        $attributes['items'] = null;
        /** @var ArgumentInterface $attributeInputs */
        $attributeInputs = $args['attributes'];
        foreach ($attributeInputs as $attribute) {
            if (!isset($attribute['attribute_code']) || !isset($attribute['entity_type'])) {
                $attributes['items'][] = $this->createInputException($attribute);
                continue;
            }
            try {
                $type = $this->type->getType($attribute['attribute_code'], $attribute['entity_type']);
            } catch (InputException $exception) {
                $attributes['items'][] = new GraphQlNoSuchEntityException(
                    __(
                        'Attribute code %1 of entity type %2 not configured to have a type.',
                        [$attribute['attribute_code'], $attribute['entity_type']]
                    )
                );
                continue;
            } catch (LocalizedException $exception) {
                $attributes['items'][] = new GraphQlInputException(
                    __(
                        'Invalid entity_type specified: %1',
                        [$attribute['entity_type']]
                    )
                );
                continue;
            }

            if (empty($type)) {
                continue;
            }

            $attributes['items'][] = [
                'attribute_code' => $attribute['attribute_code'],
                'entity_type' => $attribute['entity_type'],
                'attribute_type' => ucfirst($type)
            ];
        }

        return $attributes;
    }

    /**
     * Create GraphQL input exception for an invalid AttributeInput ArgumentValueInterface
     *
     * @param array $attribute
     * @return GraphQlInputException
     */
    private function createInputException(array $attribute)
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
