<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem;

use Magento\Framework\Exception\FileSystemException;

/**
 * Provides extension for Driver interface.
 *
 * @see DriverInterface
 *
 * @deprecated Method will be moved to DriverInterface
 * @see DriverInterface
 */
interface ExtendedDriverInterface extends DriverInterface
{
    /**
     * Retrieve file metadata.
     *
     * Implementation must return associative array with next keys:
     *
     * ```
     * [
     *  'path',
     *  'dirname',
     *  'basename',
     *  'extension',
     *  'filename',
     *  'timestamp',
     *  'size',
     *  'mimetype',
     *  'extra' => [
     *      'image-width',
     *      'image-height'
     *      ]
     *  ];
     *
     * @param string $path Absolute path to file
     * @return array
     * @throws FileSystemException
     *
     * @deprecated Method will be moved to DriverInterface
     */
    public function getMetadata(string $path): array;
}
