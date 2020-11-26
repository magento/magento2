<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAnalytics\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Data Provider for the Admin usage UI component.
 */
class AdminUsageNotificationDataProvider extends AbstractDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        return null;
    }
}
