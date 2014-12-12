<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
