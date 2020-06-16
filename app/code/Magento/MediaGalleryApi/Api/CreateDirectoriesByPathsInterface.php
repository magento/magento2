<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Create folders by provided paths
 * @api
 */
interface CreateDirectoriesByPathsInterface
{
    /**
     * Create new directories by provided paths
     *
     * @param string[] $paths
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $paths): void;
}
