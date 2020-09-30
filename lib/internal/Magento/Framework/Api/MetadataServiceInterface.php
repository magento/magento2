<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * MetadataService returns custom attribute metadata for a given class or interface it implements
 *
 * @api
 * @since 100.0.2
 */
interface MetadataServiceInterface
{
    /**
     * Get custom attribute metadata for the given class or interfaces it implements.
     *
     * @param string|null $dataObjectClassName Data object class name
     * @return \Magento\Framework\Api\MetadataObjectInterface[]
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null);
}
