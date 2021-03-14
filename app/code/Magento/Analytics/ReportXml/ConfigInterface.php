<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

/**
 * Interface ConfigInterface
 *
 * Interface for ReportXml Config
 *
 * @deprecated 103.0.2
 * @see \Magento\Framework\Config\DataInterface
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
