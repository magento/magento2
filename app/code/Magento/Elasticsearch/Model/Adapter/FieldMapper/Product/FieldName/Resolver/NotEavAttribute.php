<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;

/**
 * Resolver field name for not EAV attribute.
 */
class NotEavAttribute extends Resolver implements ResolverInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param ResolverInterface $resolver
     * @param Config $eavConfig
     */
    public function __construct(ResolverInterface $resolver, Config $eavConfig)
    {
        parent::__construct($resolver);
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = []): string
    {
        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        if (!$attribute) {
            return $attributeCode;
        }

        return $this->getNext()->getFieldName($attributeCode, $context);
    }
}
