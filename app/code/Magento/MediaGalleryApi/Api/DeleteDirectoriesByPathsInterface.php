<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Delete folder by provided path
 * @api
 */
interface DeleteDirectoriesByPathsInterface
{
    /**
     * Deletes the existing folder
     *
     * @param string[] $paths
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(array $paths): void;
}
