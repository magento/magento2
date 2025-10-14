<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Constructor
     *
     * @param CustomerMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        CustomerMetadata $metadata,
        ?AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
