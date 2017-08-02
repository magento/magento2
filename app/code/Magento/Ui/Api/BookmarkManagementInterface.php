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
 * @since 2.0.0
 */
interface BookmarkManagementInterface
{
    /**
     * Retrieve list of bookmarks by namespace
     *
     * @param string $namespace
     * @return \Magento\Ui\Api\Data\BookmarkInterface[]
     * @since 2.0.0
     */
    public function loadByNamespace($namespace);

    /**
     * Retrieve bookmark by identifier and namespace
     *
     * @param string $identifier
     * @param string $namespace
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function getByIdentifierNamespace($identifier, $namespace);
}
