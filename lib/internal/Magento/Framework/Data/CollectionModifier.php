<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * It is pool of collection conditions, which can be add to
 * Product Collection.
 * This class was created, as extension point, in order to resolve problem with area specific plugins, which
 * listens product collection. F.E. this class allows to apply stock filter not only for frontend area
 * but for other areas for product collection too
 */
class CollectionModifier implements CollectionModifierInterface
{
    /**
     * @var CollectionModifierInterface[]
     */
    private $conditions;

    /**
     * CollectionConditionApplier constructor.
     * @param array $conditions
     */
    public function __construct(
        array $conditions
    ) {
        $this->conditions = $conditions;
    }

    /**
     * Composite method, which apply different product conditions
     * you can register new condition in module/di.xml
     *
     * @param AbstractDb $collection
     * @return void
     */
    public function apply(AbstractDb $collection)
    {
        foreach ($this->conditions as $condition) {
            $condition->apply($collection);
        }
    }
}
