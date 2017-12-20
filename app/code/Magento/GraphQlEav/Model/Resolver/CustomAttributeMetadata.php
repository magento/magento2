<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlEav\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\ArgumentInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\Exception\InputException;
use \Magento\GraphQl\Model\ResolverContextInterface;

/**
 * Resolve data for custom attribute metadata requests
 */
class CustomAttributeMetadata implements ResolverInterface
{
    /**
     * @var CustomAttributeTypeLocatorInterface
     */
    private $typeLocator;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @param CustomAttributeTypeLocatorInterface $typeLocator
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        CustomAttributeTypeLocatorInterface $typeLocator,
        TypeProcessor $typeProcessor
    ) {
        $this->typeLocator = $typeLocator;
        $this->typeProcessor = $typeProcessor;
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
            try {
                $type = $this->typeLocator->getType($attribute['attribute_code'], $attribute['entity_type']);
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__($e->getMessage()));
            }

            $isComplexType = strpos($type, '\\') !== false;
            if ($type === TypeProcessor::ANY_TYPE) {
                continue;
            } elseif ($isComplexType) {
                try {
                    $type = $this->typeProcessor->translateTypeName($type);
                } catch (\InvalidArgumentException $exception) {
                    throw new GraphQlInputException(
                        __('Type %1 has no internal representation declared.', $type),
                        null,
                        0,
                        false
                    );
                }
            }

            $attributes['items'][] = [
                'attribute_code' => $attribute['attribute_code'],
                'entity_type' => $attribute['entity_type'],
                'attribute_type' => ucfirst($type)
            ];
        }

        return $attributes;
    }
}
