<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;

use Magento\Framework\Api\MetadataServiceInterface;

interface GetCustomAttributeCodesInterface
{
    /**
     * Receive a list of custom EAV attributes using provided metadata service.
     *
     * @param MetadataServiceInterface $metadataService Custom attribute metadata service to be used
     * @return string[]
     */
    public function execute(MetadataServiceInterface $metadataService): array;
}
