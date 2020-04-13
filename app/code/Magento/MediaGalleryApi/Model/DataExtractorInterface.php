<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model;

/**
 * Extract data from an object using available getters
 */
interface DataExtractorInterface
{
    /**
     * Extract data from an object using available getters (does not process extension attributes)
     *
     * @param object $object
     * @param string|null $interface
     * @return array
     */
    public function extract($object, string $interface = null): array;
}
