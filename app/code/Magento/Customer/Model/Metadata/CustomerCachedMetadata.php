<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
