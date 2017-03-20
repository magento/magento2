<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

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
