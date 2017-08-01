<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Model\Config;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class \Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AttributeSetEntityTypeCodeFilter
 *
 * @since 2.2.0
 */
class AttributeSetEntityTypeCodeFilter implements CustomFilterInterface
{
    /**
     * @var Config
     * @since 2.2.0
     */
    private $eavConfig;

    /**
     * @param Config $eavConfig
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $entityType = $this->eavConfig->getEntityType($filter->getValue());

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection->setEntityTypeFilter($entityType->getId());

        return true;
    }
}
