<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute\Group;

use Magento\Cms\Model\Template\Filter;

/**
 * Eav attribute group resource collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource model for collection
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Eav\Model\Entity\Attribute\Group::class,
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group::class
        );
    }

    /**
     * Set Attribute Set Filter
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function setAttributeSetFilter($filter)
    {
        $this->addFieldToFilter('attribute_set_id', [$filter->getConditionType() => $filter->getValue()]);
        $this->setOrder('sort_order');
        return $this;
    }

    /**
     * Set sort order
     *
     * @param string $direction
     * @return $this
     * @codeCoverageIgnore
     */
    public function setSortOrder($direction = self::SORT_ORDER_ASC)
    {
        return $this->addOrder('sort_order', $direction);
    }
}
