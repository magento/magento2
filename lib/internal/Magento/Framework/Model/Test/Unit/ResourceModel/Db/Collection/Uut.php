<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Pattern type: Public Morozov
 */
class Uut extends AbstractCollection
{
    /**
     * @return bool
     */
    public function wereFieldsToSelectChanged()
    {
        return $this->_fieldsToSelectChanged;
    }

    /**
     * @return array|null
     */
    public function getFieldsToSelect()
    {
        return $this->_fieldsToSelect;
    }

    /**
     * @param array $fields
     */
    public function setFieldsToSelect(array $fields)
    {
        $this->_fieldsToSelect = $fields;
    }

    /**
     * @param $resource
     */
    public function setResource($resource)
    {
        $this->_resource = $resource;
    }

    /**
     * @return array
     */
    public function getJoinedTables()
    {
        return $this->_joinedTables;
    }
}
