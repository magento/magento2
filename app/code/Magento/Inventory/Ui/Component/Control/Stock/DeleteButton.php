<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Ui\Component\Control\Stock;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Class DeleteButton
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var GenericButton
     */
    private $button;

    /**
     * DeleteButton constructor.
     *
     * @param GenericButton $button
     */
    public function __construct(GenericButton $button)
    {
        $this->button = $button;
    }

    /**
     * Get stock delete button data such as label, css class, on click action and sort order
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->button->getId()) {
            $data = [
                'label' => __('Delete Stock'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to delete this stock ?') . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * Get stock delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->button->getUrl('*/*/delete', [StockInterface::STOCK_ID => $this->button->getId()]);
    }
}