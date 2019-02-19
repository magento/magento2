<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\DataSavior;

/**
 * Allows to access dump, that can be persisted in any file format or in database
 */
interface DumpAccessorInterface
{
    /**
     * Allows to persist data to different sources: file, database, etc
     *
     * @param string $resource - can be for example absolute path to file
     * @param array $data - data format, in which data should be stored
     * @return void
     */
    public function save($resource, array $data);

    /**
     * Allows to read data by batches from different resources
     *
     * By resource means connection to database to absolute path to file, depends to implementation
     *
     * @param string $resource
     * @return \Generator
     */
    public function read($resource);

    /**
     * Destruct resource
     *
     * @param string $resource
     * @return void
     */
    public function destruct($resource);
}
