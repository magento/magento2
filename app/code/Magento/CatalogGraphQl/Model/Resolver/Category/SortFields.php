<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category\Attribute\Source\Sortby;
use Magento\Catalog\Model\Config;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the sort fields data
 */
class SortFields implements ResolverInterface
{
    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @var Sortby
     */
    private $sortbyAttributeSource;

    /**
     * @param Config $catalogConfig
     * @param Sortby $sortbyAttributeSource
     */
    public function __construct(
        Config $catalogConfig,
        Sortby $sortbyAttributeSource
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->sortbyAttributeSource = $sortbyAttributeSource;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $sortFieldsOptions = $this->sortbyAttributeSource->getAllOptions();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        array_walk(
            $sortFieldsOptions,
            function (&$option) {
                $option['label'] = (string)$option['label'];
            }
        );
        $data = [
            'default' => $this->catalogConfig->getProductListDefaultSortBy($storeId),
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
