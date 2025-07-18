<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

/**
 * @api
 * @since 100.0.2
 */
class Repository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    protected function buildSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter('additional_table.is_used_in_grid', 1)->create();
    }
}
