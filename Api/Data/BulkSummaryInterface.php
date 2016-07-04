<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bulk\Api\Data;

interface BulkSummaryInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BULK_ID = 'bulk_id';
    const DESCRIPTION = 'description';
    const START_TIME = 'start_time';
    /**#@-*/
    
    /**
     * @return UuidInterface
     */
    public function getBulkId();

    /**
     * @param UuidInterface $bulkId
     * @return $this
     */
    public function setBulkId($bulkId);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getStartTime();

    /**
     * @param string $timestamp
     * @return $this
     */
    public function setStartTime($timestamp);
}
