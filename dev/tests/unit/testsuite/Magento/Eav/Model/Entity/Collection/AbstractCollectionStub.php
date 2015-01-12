<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Collection;

class AbstractCollectionStub extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * Retrieve item by id
     *
     * @param   mixed $id
     * @return  \Magento\Framework\Object
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
        return $this->_init('Magento\Framework\Object', 'test_entity_model');
    }
}
