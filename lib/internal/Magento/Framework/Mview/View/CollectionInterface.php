<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\CollectionInterface
 *
 * @api
 */
interface CollectionInterface
{
    /**
     * Return views by given state mode
     *
     * @param string $mode
     * @return \Magento\Framework\Mview\ViewInterface[]
     */
    public function getViewsByStateMode($mode);

    /**
     * Search all views by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  \Magento\Framework\Mview\ViewInterface[]
     */
    public function getItemsByColumnValue($column, $value);

    /**
     * Retrieve collection views
     *
     * @return \Magento\Framework\Mview\ViewInterface[]
     */
    public function getItems();
}
