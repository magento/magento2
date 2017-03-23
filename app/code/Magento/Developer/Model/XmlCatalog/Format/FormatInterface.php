<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\XmlCatalog\Format;

/**
 * Interface FormatInterface is implemented by custom URN catalog generators
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
