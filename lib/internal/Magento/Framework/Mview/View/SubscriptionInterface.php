<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\SubscriptionInterface
 *
 * @api
 */
interface SubscriptionInterface
{
    /**
     * Create subsciption
     *
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
     */
    public function create();

    /**
     * Remove subscription
     *
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
     */
    public function remove();

    /**
     * Retrieve View related to subscription
     *
     * @return \Magento\Framework\Mview\ViewInterface
     */
    public function getView();

    /**
     * Retrieve table name
     *
     * @return string
     */
    public function getTableName();

    /**
     * Retrieve table column name
     *
     * @return string
     */
    public function getColumnName();
}
