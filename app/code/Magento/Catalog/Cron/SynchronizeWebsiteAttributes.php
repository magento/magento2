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
 * @since 2.2.0
 */
class SynchronizeWebsiteAttributes
{
    /**
     * @var WebsiteAttributesSynchronizer
     * @since 2.2.0
     */
    private $synchronizer;

    /**
     * SynchronizeWebsiteAttributes constructor.
     * @param WebsiteAttributesSynchronizer $synchronizer
     * @since 2.2.0
     */
    public function __construct(WebsiteAttributesSynchronizer $synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    /**
     * Synchronizes website attribute values if needed
     * @return void
     * @since 2.2.0
     */
    public function execute()
    {
        if ($this->synchronizer->isSynchronizationRequired()) {
            $this->synchronizer->synchronize();
        }
    }
}
