<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Directory\Command;

/**
 * Delete folder by provided path
 * @api
 */
interface DeleteByPathInterface
{
    /**
     * Deletes the existing folder
     *
     * @param string $path
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(string $path): void;
}
