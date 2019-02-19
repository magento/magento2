<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Api;

/**
 * Interface for managing bookmarks
 *
 * @api
 * @since 100.0.2
 */
interface BookmarkManagementInterface
{
    /**
     * Retrieve list of bookmarks by namespace
     *
     * @param string $namespace
     * @return \Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function loadByNamespace($namespace);

    /**
     * Retrieve bookmark by identifier and namespace
     *
     * @param string $identifier
     * @param string $namespace
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function getByIdentifierNamespace($identifier, $namespace);
}
