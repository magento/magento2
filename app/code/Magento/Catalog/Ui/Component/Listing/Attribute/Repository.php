<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

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
