<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

/**
 * Class provides information about MySQL configuration settings.
 */
class DbConfigurationHelper
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var int
     */
    private $incrementValue;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get value of auto_increment_increment variable.
     *
     * @return int
     */
    public function getAutoIncrementIncrement()
    {
        if ($this->incrementValue === null) {
            $increment = $this->resource->getConnection()->fetchRow('SHOW VARIABLES LIKE "auto_increment_increment"');
            $this->incrementValue = !empty($increment['Value']) ? (int)$increment['Value'] : 1;
        }
        return $this->incrementValue;
    }
}
