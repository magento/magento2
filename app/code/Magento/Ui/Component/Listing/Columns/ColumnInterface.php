<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ColumnInterface
 */
interface ColumnInterface extends UiComponentInterface
{
    /**
     * To prepare items of a column
     *
     * @param array $items
     * @return array
     */
    public function prepareItems(array & $items);
}
