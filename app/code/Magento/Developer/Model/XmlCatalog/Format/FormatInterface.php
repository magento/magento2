<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Developer\Model\XmlCatalog\Format;

/**
 * Interface FormatInterface is implemented by custom URN catalog generators
 *
 * @api
 * @since 100.0.2
 */
interface FormatInterface
{
    /**
     * Generate Catalog of URNs
     *
     * @param string[] $dictionary
     * @param string $configFile absolute path to the file to write the catalog
     * @return void
     */
    public function generateCatalog(array $dictionary, $configFile);
}
