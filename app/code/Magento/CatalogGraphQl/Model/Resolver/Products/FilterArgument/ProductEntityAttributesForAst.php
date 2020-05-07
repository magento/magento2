<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\FilterArgument;

use Magento\Framework\GraphQl\Config\Element\InterfaceType;
use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\FieldEntityAttributesInterface;

/**
 * Retrieves attributes for a field for the ast converter
 */
class ProductEntityAttributesForAst implements FieldEntityAttributesInterface
{
    private const PRODUCT_BASE_TYPE = 'SimpleProduct';

    private const PRODUCT_FILTER_INPUT = 'ProductAttributeFilterInput';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Additional attributes that are not retrieved by getting fields from ProductInterface
     *
     * @var array
     */
    private $additionalAttributes = ['min_price', 'max_price', 'category_id'];

    /**
     * @param ConfigInterface $config
     * @param string[] $additionalAttributes
     */
    public function __construct(
        ConfigInterface $config,
        array $additionalAttributes = []
    ) {
        $this->config = $config;
        $this->additionalAttributes = array_merge($this->additionalAttributes, $additionalAttributes);
    }

    /**
     * @inheritdoc
     *
     * Gather all the product entity attributes that can be filtered by search criteria.
     * Example format ['attributeNameInGraphQl' => ['type' => 'String'. 'fieldName' => 'attributeNameInSearchCriteria']]
     *
     * @return array
     */
    public function getEntityAttributes() : array
    {
        $productTypeSchema = $this->config->getConfigElement(self::PRODUCT_BASE_TYPE);
        if (!$productTypeSchema instanceof Type) {
            throw new \LogicException(__("%1 type not defined in schema.", self::PRODUCT_BASE_TYPE));
        }

        $fields = [];
        foreach ($productTypeSchema->getInterfaces() as $interface) {
            /** @var InterfaceType $configElement */
            $configElement = $this->config->getConfigElement($interface['interface']);

            foreach ($configElement->getFields() as $field) {
                $fields[$field->getName()] = [
                    'type' => 'String',
                    'fieldName' => $field->getName(),
                ];
            }
        }

        $productAttributeFilterFields = $this->getProductAttributeFilterFields();
        $fields = array_merge($fields, $productAttributeFilterFields);

        foreach ($this->additionalAttributes as $attributeName) {
            $fields[$attributeName] = [
                'type' => 'String',
                'fieldName' => $attributeName,
            ];
        }

        return $fields;
    }

    /**
     * Get fields from ProductAttributeFilterInput
     *
     * @return array
     */
    private function getProductAttributeFilterFields()
    {
        $filterFields = [];

        $productAttributeFilterSchema = $this->config->getConfigElement(self::PRODUCT_FILTER_INPUT);
        $productAttributeFilterFields = $productAttributeFilterSchema->getFields();
        foreach ($productAttributeFilterFields as $filterField) {
            $filterFields[$filterField->getName()] = [
                'type' => 'String',
                'fieldName' => $filterField->getName(),
            ];
        }
        return $filterFields;
    }
}
