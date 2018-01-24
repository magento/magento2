<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\ArgumentInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\Exception\InputException;
use \Magento\GraphQl\Model\ResolverContextInterface;
use Magento\EavGraphQl\Model\Resolver\Query\Type;

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
    public function resolve(array $args, ResolverContextInterface $context)
    {
        if (!isset($args['attributes']) || empty($args['attributes'])) {
            throw new InputException(__('Missing arguments for correct type resolution.'));
        }

        $attributes['items'] = null;
        /** @var ArgumentInterface $attributeInputs */
        $attributeInputs = $args['attributes'];
        foreach ($attributeInputs->getValue() as $attribute) {
            $type = $this->type->getType($attribute['attribute_code'], $attribute['entity_type']);

            if (!empty($type)) {
                $attributes['items'][] = [
                    'attribute_code' => $attribute['attribute_code'],
                    'entity_type' => $attribute['entity_type'],
                    'attribute_type' => ucfirst($type)
                ];
            }
        }

        return $attributes;
    }
}
