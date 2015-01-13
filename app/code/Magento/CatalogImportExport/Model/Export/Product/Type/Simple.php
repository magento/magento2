<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export\Product\Type;

/**
 * Export entity product type simple model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Simple extends \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType
{
    /**
     * Overridden attributes parameters.
     *
     * @var array
     */
    protected $_attributeOverrides = [
        'has_options' => ['source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'],
        'required_options' => ['source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'],
        'created_at' => ['backend_type' => 'datetime'],
        'updated_at' => ['backend_type' => 'datetime'],
    ];

    /**
     * Array of attributes codes which are disabled for export.
     *
     * @var string[]
     */
    protected $_disabledAttrs = [
        'old_id',
        'tier_price',
        'group_price',
        'category_ids',
    ];
}
