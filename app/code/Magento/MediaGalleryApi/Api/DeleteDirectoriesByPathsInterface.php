<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Delete folders by provided paths
 * @api
 * @since 101.0.0
 */
interface DeleteDirectoriesByPathsInterface
{
    /**
     * Deletes the existing folders
     *
     * @param string[] $paths
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @since 101.0.0
     */
    public function execute(array $paths): void;
}
