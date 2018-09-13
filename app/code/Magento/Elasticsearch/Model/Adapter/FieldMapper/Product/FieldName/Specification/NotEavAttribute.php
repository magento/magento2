<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Specification;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;

/**
 * Class ProductFieldMapper
 */
class NotEavAttribute extends Specification implements SpecificationInterface
{
    const TYPE = 'NOT_EAV_ATTRIBUTE';

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param SpecificationInterface $specification
     * @param Config $eavConfig
     */
    public function __construct(SpecificationInterface $specification, Config $eavConfig)
    {
        parent::__construct($specification);
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $attributeCode): string
    {
        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        if (!$attribute) {
            return self::TYPE;
        }

        return $this->getNext()->resolve($attributeCode);
    }
}
