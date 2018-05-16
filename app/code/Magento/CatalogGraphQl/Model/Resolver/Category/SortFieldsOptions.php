<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the sort fields options information object
 */
class SortFieldsOptions implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * @param ValueFactory $valueFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(
        ValueFactory $valueFactory,
        \Magento\Catalog\Model\Config $catalogConfig
    ) {
        $this->valueFactory = $valueFactory;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $sortFieldsOptions = [
            ['key' => 'position', 'label' => 'Position']
        ];
        foreach ($this->catalogConfig->getAttributesUsedForSortBy() as $attribute) {
            $sortFieldsOptions[] = ['key' => $attribute->getAttributeCode(), 'label' => $attribute->getStoreLabel()];
        }
        
        $result = function () use ($sortFieldsOptions) {
            return $sortFieldsOptions;
        };

        return $this->valueFactory->create($result);
    }
}
