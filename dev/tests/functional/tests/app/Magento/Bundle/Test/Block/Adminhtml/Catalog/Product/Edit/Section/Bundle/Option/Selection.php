<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option;

use Magento\Mtf\Block\Form;

/**
 * Assigned product row to bundle option.
 */
class Selection extends Form
{
    /**
     * Fill data to product row.
     *
     * @param array $fields
     * @return void
     */
    public function fillProductRow(array $fields)
    {
        unset($fields['product_id']);
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Get data item selection.
     *
     * @param array $fields
     * @return array
     */
    public function getProductRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $newFields = $this->_getData($mapping);
        if (isset($mapping['getProductName'])) {
            $newFields['getProductName'] = $this->_rootElement->find(
                $mapping['getProductName']['selector'],
                $mapping['getProductName']['strategy']
            )->getText();
        }
        return $newFields;
    }
}
