<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Create folder by provided path
 * @api
 */
interface CreateDirectoriesByPathsInterface
{
    /**
     * Create new directory by provided path
     *
     * @param string[] $paths
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $paths): void;
}
