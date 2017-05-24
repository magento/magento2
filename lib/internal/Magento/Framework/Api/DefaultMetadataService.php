<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
