<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\ElementInterface;

/**
 * Form item samples.
 */
class SampleRow extends Form
{
    /**
     * Sort draggable handle.
     *
     * @var string
     */
    protected $sortDraggableHandle = '*[class=draggable-handle]';

    /**
     * Fill item sample.
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
     * Get data item sample.
     *
     * @param array $fields
     * @return array
     */
    public function getDataSampleRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        return $this->_getData($mapping);
    }

    /**
     * Drag and drop block element to specific target.
     *
     * @param ElementInterface $target
     * @return void
     */
    public function dragAndDropTo(ElementInterface $target)
    {
        $this->getSortHandle()->dragAndDrop($target);
    }

    /**
     * Get sort handle.
     *
     * @return ElementInterface
     */
    public function getSortHandle()
    {
        return $this->_rootElement->find($this->sortDraggableHandle);
    }
}
