<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Default implementation of metadata service, which does not return any real attributes.
 * @since 2.0.0
 */
class DefaultMetadataService implements MetadataServiceInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        return [];
    }
}
