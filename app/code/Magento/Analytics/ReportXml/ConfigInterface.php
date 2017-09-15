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
 * @since 2.2.0
 */
interface ConfigInterface
{
    /**
     * Config of ReportXml
     *
     * @param string $queryName
     * @return array
     * @since 2.2.0
     */
    public function get($queryName);
}
