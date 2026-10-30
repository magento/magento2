<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

/**
 * Interface ConfigInterface
 *
 * Interface for ReportXml Config
 */
interface ConfigInterface
{
    /**
     * Config of ReportXml
     *
     * @param string $queryName
     * @return array
     */
    public function get($queryName);
}
