<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Config\Source;

use Magento\Catalog\Model\Config;
use Magento\Framework\Option\ArrayInterface;

/**
 * @inheritdoc
 */
class ListSort implements ArrayInterface
{
    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @param Config $catalogConfig
     */
    public function __construct(Config $catalogConfig)
    {
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('Relevance'),
            'value' => 'relevance'
        ];
        foreach ($this->catalogConfig->getAttributesUsedForSortBy() as $attribute) {
            $options[] = [
                'label' => __($attribute['frontend_label']),
                'value' => $attribute['attribute_code']
            ];
        }

        return $options;
    }
}
