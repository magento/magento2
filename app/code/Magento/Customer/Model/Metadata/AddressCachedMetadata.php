<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;

/**
 * Cached customer address attribute metadata
 * @since 2.0.0
 */
class AddressCachedMetadata extends CachedMetadata implements AddressMetadataInterface
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $entityType = 'customer_address';

    /**
     * Constructor
     *
     * @param AddressMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     * @since 2.0.0
     */
    public function __construct(
        AddressMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
