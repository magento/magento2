<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Cached customer attribute metadata service
 * @since 2.0.0
 */
class CustomerCachedMetadata extends CachedMetadata implements CustomerMetadataInterface
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $entityType = 'customer';

    /**
     * Constructor
     *
     * @param CustomerMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     * @since 2.0.0
     */
    public function __construct(
        CustomerMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
