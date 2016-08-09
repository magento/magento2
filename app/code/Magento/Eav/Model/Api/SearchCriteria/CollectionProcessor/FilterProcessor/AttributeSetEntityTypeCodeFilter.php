<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Model\Config;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class AttributeSetEntityTypeCodeFilter implements CustomFilterInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Apply entity type code filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        if ($filter->getField() == 'entity_type_code') {
            $entityTypeCode = $filter->getValue();
            $entityType = $this->eavConfig->getEntityType($entityTypeCode);
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
            $collection->setEntityTypeFilter($entityType->getId());
            return true;
        }
        return false;
    }
}
