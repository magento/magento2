<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Storage;

/**
 * Get filtered contents from a listing service.
 */
class GetCleanedContents
{
    /**
     * Get filtered contents from a listing.
     *
     * @param array $contents
     * @return array
     */
    public function execute(array $contents): array
    {
        $cachedProperties = array_flip([
            'path',
            'dirname',
            'basename',
            'extension',
            'filename',
            'size',
            'mimetype',
            'visibility',
            'timestamp',
            'type',
            'md5',
        ]);

        foreach ($contents as $path => $object) {
            if (is_array($object)) {
                $contents[$path] = array_intersect_key($object, $cachedProperties);
            }
        }

        return $contents;
    }
}
