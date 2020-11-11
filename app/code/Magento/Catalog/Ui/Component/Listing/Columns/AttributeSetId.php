<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Listing\Columns;

/**
 * AttributeSetId listing column component.
 */
class AttributeSetId extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @inheritDoc
     */
    protected function applySorting()
    {
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
            && in_array(strtoupper($sorting['direction']), ['ASC', 'DESC'], true)
        ) {
            $collection = $this->getContext()->getDataProvider()->getCollection();
            $collection->joinField(
                'attribute_set',
                'eav_attribute_set',
                'attribute_set_name',
                'attribute_set_id=attribute_set_id',
                null,
                'left'
            );
            $collection->getSelect()->order('attribute_set_name ' . $sorting['direction']);
        }
    }
}
