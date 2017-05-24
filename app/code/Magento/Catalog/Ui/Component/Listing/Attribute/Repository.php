<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

/**
 * @api
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
