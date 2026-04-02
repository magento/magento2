<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

/**
 * File reader
 * @api
 */
interface ReadFileInterface
{
    /**
     * Create file object from the file
     *
     * @param string $path
     * @return FileInterface
     */
    public function execute(string $path): FileInterface;
}
