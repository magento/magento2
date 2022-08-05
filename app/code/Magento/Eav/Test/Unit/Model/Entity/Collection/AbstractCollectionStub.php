<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Collection;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DataObject;

class AbstractCollectionStub extends AbstractCollection
{
    /**
     * Retrieve item by id
     *
     * @param   mixed $id
     * @return  DataObject
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
        return $this->_init(DataObject::class, 'test_entity_model');
    }

    /**
     * Retrieve collection empty item
     *
     * @return DataObject
     */
    public function getNewEmptyItem()
    {
        return new DataObject();
    }
}
