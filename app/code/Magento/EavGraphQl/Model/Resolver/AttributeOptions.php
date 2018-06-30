<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve attribute options data for custom attribute.
 */
class AttributeOptions implements ResolverInterface
{
    /**
     * @var AttributeOptionManagementInterface
     */
    protected $optionManager;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * AttributeOptions constructor.
     *
     * @param AttributeOptionManagementInterface $optionManager
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        AttributeOptionManagementInterface $optionManager,
        ValueFactory $valueFactory
    ) {
        $this->optionManager = $optionManager;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {
        $options = [];

        $entityType = !empty($value['entity_type']) ? $value['entity_type'] : '';
        $attributeCode = !empty($value['attribute_code']) ? $value['attribute_code'] : '';

        try {
            /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $attributeOptions */
            $attributeOptions = $this->optionManager->getItems($entityType, $attributeCode);
        } catch (\Exception $e) {
            $attributeOptions = [];
        }

        if (is_array($attributeOptions)) {
            /** @var \Magento\Eav\Api\Data\AttributeOptionInterface $option */
            foreach ($attributeOptions as $option) {
                if (!$option->getValue()) {
                    continue;
                }

                $options[] = [
                    'label' => $option->getLabel(),
                    'value' => $option->getValue()
                ];
            }
        }

        $result = function () use ($options) {
            return $options;
        };

        return $this->valueFactory->create($result);
    }
}
