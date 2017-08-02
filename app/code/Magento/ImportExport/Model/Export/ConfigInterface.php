<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export;

/**
 * Provides export configuration
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
     * Retrieve export file formats configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getFileFormats();

    /**
     * Retrieve import entity types configuration
     *
     * @param string $entity
     * @return array
     * @since 2.0.0
     */
    public function getEntityTypes($entity);
}
