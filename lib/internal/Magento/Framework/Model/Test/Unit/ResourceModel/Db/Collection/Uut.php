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
    public function wereFieldsToSelectChanged()
    {
        return $this->_fieldsToSelectChanged;
    }

    public function getFieldsToSelect()
    {
        return $this->_fieldsToSelect;
    }

    public function setFieldsToSelect(array $fields)
    {
        $this->_fieldsToSelect = $fields;
    }

    public function setResource($resource)
    {
        $this->_resource = $resource;
    }

    public function getJoinedTables()
    {
        return $this->_joinedTables;
    }
}
