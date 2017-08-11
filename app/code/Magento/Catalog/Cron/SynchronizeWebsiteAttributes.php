<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Cron;

use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;

/**
 * Class SynchronizeWebsiteAttributes
 * @package Magento\Catalog\Cron
 */
class SynchronizeWebsiteAttributes
{
    /**
     * @var WebsiteAttributesSynchronizer
     */
    private $synchronizer;

    /**
     * SynchronizeWebsiteAttributes constructor.
     * @param WebsiteAttributesSynchronizer $synchronizer
     */
    public function __construct(WebsiteAttributesSynchronizer $synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    /**
     * Synchronizes website attribute values if needed
     * @return void
     */
    public function execute()
    {
        if ($this->synchronizer->isSynchronizationRequired()) {
            $this->synchronizer->synchronize();
        }
    }
}
