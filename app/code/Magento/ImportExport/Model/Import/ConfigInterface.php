<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;

/**
 * Provides import configuration
 *
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Retrieve import entities configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getEntities();

    /**
     * Retrieve import entity types configuration
     *
     * @param string $entity
     * @return array
     * @since 2.0.0
     */
    public function getEntityTypes($entity);

    /**
     * Retrieve a list of indexes which are affected by import of the specified entity.
     *
     * @param string $entity
     * @return array
     * @since 2.0.0
     */
    public function getRelatedIndexers($entity);
}
