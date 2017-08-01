<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel;

/**
 * @api
 * Interface GridInterface
 * @since 2.0.0
 */
interface GridInterface
{
    /**
     * Adds new rows to the grid.
     *
     * Only rows that correspond to $value and $field parameters should be added.
     *
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     * @since 2.0.0
     */
    public function refresh($value, $field = null);

    /**
     * Adds new rows to the grid.
     *
     * Only rows created/updated since the last method call should be added.
     *
     * @return \Zend_Db_Statement_Interface
     * @since 2.0.0
     */
    public function refreshBySchedule();

    /**
     * @param int|string $value
     * @param null|string $field
     * @return int
     * @since 2.0.0
     */
    public function purge($value, $field = null);
}
