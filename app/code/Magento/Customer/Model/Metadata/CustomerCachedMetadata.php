<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var string
     */
    protected $entityType = 'customer';

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
