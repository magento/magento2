<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Ui\DataProvider\Attributes;

/**
 * Add the filter condition for configurable product attribute collection
 */
class UpdateConfigurableProductAttributeCollection
{
    /**
     * Adding a field to filter in existing configurable product attribute collection.
     *
     * @param Attributes $subject
     * @return void
     */
    public function beforeGetData(Attributes $subject): void
    {
        $subject->getCollection()->getSelect()->where(
            '(`apply_to` IS NULL) OR
                (
                    FIND_IN_SET(' .
                        sprintf("'%s'", Type::TYPE_SIMPLE) . ',
                        `apply_to`
                    ) AND
                    FIND_IN_SET(' .
                        sprintf("'%s'", Type::TYPE_VIRTUAL) . ',
                        `apply_to`
                    ) AND
                    FIND_IN_SET(' .
                        sprintf("'%s'", Configurable::TYPE_CODE) . ',
                        `apply_to`
                    )
                 )'
        );
    }
}
