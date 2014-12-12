<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
