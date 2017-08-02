<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;

/**
 * Cached customer address attribute metadata
 */
class AddressCachedMetadata extends CachedMetadata implements AddressMetadataInterface
{
    /**
     * @var string
     * @since 2.0.9
     */
    protected $entityType = 'customer_address';

    /**
     * Constructor
     *
     * @param AddressMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        AddressMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
