<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl;

/**
 * Stub for version control abstract collection model.
 */
class AbstractCollectionStub extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection
{
    /**
     * Retrieve item by id
     *
     * @param   mixed $id
     * @return  \Magento\Framework\DataObject
     */
    public function getItemById($id)
    {
        if (isset($this->_itemsById[$id])) {
            return $this->_itemsById[$id];
        }
        return null;
    }

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        return $this->_init('Magento\Framework\DataObject', 'test_entity_model');
    }
}
