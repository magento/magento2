<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\CollectionInterface
 *
 * @since 2.0.0
 */
interface CollectionInterface
{
    /**
     * Return views by given state mode
     *
     * @param string $mode
     * @return \Magento\Framework\Mview\ViewInterface[]
     * @since 2.0.0
     */
    public function getViewsByStateMode($mode);

    /**
     * Search all views by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  \Magento\Framework\Mview\ViewInterface[]
     * @since 2.0.0
     */
    public function getItemsByColumnValue($column, $value);

    /**
     * Retrieve collection views
     *
     * @return \Magento\Framework\Mview\ViewInterface[]
     * @since 2.0.0
     */
    public function getItems();
}
