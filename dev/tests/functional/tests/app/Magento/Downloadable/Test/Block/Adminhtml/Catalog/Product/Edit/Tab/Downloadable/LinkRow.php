<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Mtf\Block\Form;

/**
 * Class LinkRow
 *
 * Form item links
 */
class LinkRow extends Form
{
    /**
     * Delete button selector
     *
     * @var string
     */
    protected $deleteButton = '.delete-link-item';

    /**
     * Fill item link
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
     * Get data item link
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
     * Click delete button
     *
     * @return void
     */
    public function clickDeleteButton()
    {
        $this->_rootElement->find($this->deleteButton)->click();
    }
}
