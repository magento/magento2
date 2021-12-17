<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Model;

use Magento\ConfigurableProduct\Ui\DataProvider\Attributes;

/**
 * Update configurable product attribute collection.
 */
class UpdateConfigurableProductAttributeCollection
{
    /**
     * Adding a field to filter in existing configurable product attribute collection.
     *
     * @param Attributes $subject
     * @return object
     */
    public function beforeGetData(Attributes $subject): object
    {
        $types = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        ];
        return $subject->getCollection()->addFieldToFilter(
            ['apply_to', 'apply_to'],
            [
                ['null' => true],
                ['like' => '%' . implode(',', $types) . '%']
            ]
        );
    }
}
