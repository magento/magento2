<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param string $path
     * @return void
     */
    public function generateCatalog(array $dictionary, $path);
}