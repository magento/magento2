<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Api;

/**
 * Default implementation of metadata service, which does not return any real attributes.
 */
class DefaultMetadataService implements MetadataServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        return [];
    }
}
