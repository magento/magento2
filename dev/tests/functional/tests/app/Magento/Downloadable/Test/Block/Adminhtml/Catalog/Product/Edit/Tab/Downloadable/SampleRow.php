<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Mtf\Block\Form;

/**
 * Class SampleRow
 * Form item samples
 */
class SampleRow extends Form
{
    /**
     * Fill item sample
     *
     * @param array $fields
     * @return void
     */
    public function fillSampleRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Get data item sample
     *
     * @param array $fields
     * @return array
     */
    public function getDataSampleRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        return $this->_getData($mapping);
    }
}
