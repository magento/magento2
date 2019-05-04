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
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $additionalAttributes = ['min_price', 'max_price', 'category_id'];

    /**
     * @param ConfigInterface $config
     * @param array $additionalAttributes
     */
    public function __construct(
        ConfigInterface $config,
        array $additionalAttributes = []
    ) {
        $this->config = $config;
        $this->additionalAttributes = array_merge($this->additionalAttributes, $additionalAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityAttributes() : array
    {
        $productTypeSchema = $this->config->getConfigElement('SimpleProduct');
        if (!$productTypeSchema instanceof Type) {
            throw new \LogicException(__("SimpleProduct type not defined in schema."));
        }

        $fields = [];
        foreach ($productTypeSchema->getInterfaces() as $interface) {
            /** @var InterfaceType $configElement */
            $configElement = $this->config->getConfigElement($interface['interface']);

            foreach ($configElement->getFields() as $field) {
                $fields[$field->getName()] = 'String';
            }
        }

        foreach ($this->additionalAttributes as $attribute) {
            $fields[$attribute] = 'String';
        }

        return array_keys($fields);
    }
}
