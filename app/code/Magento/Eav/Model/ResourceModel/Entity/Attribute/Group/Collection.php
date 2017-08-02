<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute\Group;

/**
 * Eav attribute group resource collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource model for collection
     *
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @param int $setId
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeSetFilter($setId)
    {
        $this->addFieldToFilter('attribute_set_id', ['eq' => $setId]);
        $this->setOrder('sort_order');
        return $this;
    }

    /**
     * Set sort order
     *
     * @param string $direction
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setSortOrder($direction = self::SORT_ORDER_ASC)
    {
        return $this->addOrder('sort_order', $direction);
    }
}
