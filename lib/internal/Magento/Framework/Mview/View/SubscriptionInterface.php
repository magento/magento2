<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\SubscriptionInterface
 *
 * @since 2.0.0
 */
interface SubscriptionInterface
{
    /**
     * Create subsciption
     *
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
     * @since 2.0.0
     */
    public function create();

    /**
     * Remove subscription
     *
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
     * @since 2.0.0
     */
    public function remove();

    /**
     * Retrieve View related to subscription
     *
     * @return \Magento\Framework\Mview\ViewInterface
     * @since 2.0.0
     */
    public function getView();

    /**
     * Retrieve table name
     *
     * @return string
     * @since 2.0.0
     */
    public function getTableName();

    /**
     * Retrieve table column name
     *
     * @return string
     * @since 2.0.0
     */
    public function getColumnName();
}
