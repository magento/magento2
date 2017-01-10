<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * Form item links.
 */
class LinkRow extends Form
{
    /**
     * Delete button selector.
     *
     * @var string
     */
    protected $deleteButton = 'button[data-action="remove_row"]';

    /**
     * Sort draggable handle.
     *
     * @var string
     */
    protected $sortDraggableHandle = '*[class=draggable-handle]';

    /**
     * Fill item link.
     *
     * @param array $fields
     * @return void
     */
    public function fillLinkRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Get data item link.
     *
     * @param array $fields
     * @return array
     */
    public function getDataLinkRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        return $this->_getData($mapping);
    }

    /**
     * Click delete button.
     *
     * @return void
     */
    public function clickDeleteButton()
    {
        $this->_rootElement->find($this->deleteButton)->click();
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
