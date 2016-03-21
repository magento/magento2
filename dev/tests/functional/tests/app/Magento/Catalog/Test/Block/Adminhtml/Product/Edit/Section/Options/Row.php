<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\ElementInterface;

/**
 * Select options row.
 */
class Row extends Form
{
    /**
     * Sort draggable handle.
     *
     * @var string
     */
    protected $sortDraggableHandle = '*[class=draggable-handle]';

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
