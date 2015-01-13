<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Cached customer attribute metadata service
 */
class CustomerCachedMetadata extends CachedMetadata implements CustomerMetadataInterface
{
    /**
     * Initialize dependencies.
     *
     * @param CustomerMetadata $metadata
     */
    public function __construct(CustomerMetadata $metadata)
    {
        $this->metadata = $metadata;
    }
}
