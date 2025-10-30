<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\ImportExport\Model\Import;

/**
 * Provides import configuration
 *
 * @api
 * @since 100.0.2
 */
interface ConfigInterface
{
    /**
     * Retrieve import entities configuration
     *
     * @return array
     */
    public function getEntities();

    /**
     * Retrieve import entity types configuration
     *
     * @param string $entity
     * @return array
     */
    public function getEntityTypes($entity);

    /**
     * Retrieve a list of indexes which are affected by import of the specified entity.
     *
     * @param string $entity
     * @return array
     */
    public function getRelatedIndexers($entity);
}
