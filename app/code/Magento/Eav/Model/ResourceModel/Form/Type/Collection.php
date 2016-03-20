<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Eav Form Type Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\ResourceModel\Form\Type;

use Magento\Eav\Model\Entity\Type;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection model
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Form\Type', 'Magento\Eav\Model\ResourceModel\Form\Type');
    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('type_id', 'label');
    }

    /**
     * Add Entity type filter to collection
     *
     * @param Type|int $entity
     * @return $this
     */
    public function addEntityTypeFilter($entity)
    {
        if ($entity instanceof Type) {
            $entity = $entity->getId();
        }

        $this->getSelect()->join(
            ['form_type_entity' => $this->getTable('eav_form_type_entity')],
            'main_table.type_id = form_type_entity.type_id',
            []
        )->where(
            'form_type_entity.entity_type_id = ?',
            $entity
        );

        return $this;
    }
}
