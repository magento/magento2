<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute\Set;

/**
 * Eav Resource Attribute Set Collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class
        );
    }

    /**
     * Add filter by entity type id to collection
     *
     * @param int $typeId
     * @return $this
     * @codeCoverageIgnore
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this->addFieldToFilter('entity_type_id', $typeId);
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('attribute_set_id', 'attribute_set_name');
    }

    /**
     * Convert collection items to select options hash array
     *
     * @return array
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('attribute_set_id', 'attribute_set_name');
    }
}
