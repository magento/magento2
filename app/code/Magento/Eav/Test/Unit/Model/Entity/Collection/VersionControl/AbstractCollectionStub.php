<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl;

use Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection;
use Magento\Framework\DataObject;

/**
 * Stub for version control abstract collection model.
 */
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
}
