<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector;

/**
 * Interface AnalyticsCommandInterface
 */
interface AnalyticsCommandInterface
{
    /**
     * @return void
     */
    public function execute();
}
