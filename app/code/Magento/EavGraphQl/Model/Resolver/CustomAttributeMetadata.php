<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\EavGraphQl\Model\Resolver\Query\FrontendType;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;

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
     * @var FrontendType
     */
    private $frontendType;

    /**
     * @param Type $type
     * @param FrontendType $frontendType
     */
    public function __construct(Type $type, FrontendType $frontendType)
    {
        $this->type = $type;
        $this->frontendType = $frontendType;
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
        foreach ($attributeInputs as $attribute) {
            if (!isset($attribute['attribute_code']) || !isset($attribute['entity_type'])) {
                $attributes['items'][] = $this->createInputException($attribute);
                continue;
            }
            try {
                $frontendType = $this->frontendType->getType($attribute['attribute_code'], $attribute['entity_type']);
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
                'attribute_type' => ucfirst($type),
                'input_type' => $frontendType
            ];
        }

        return $attributes;
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
