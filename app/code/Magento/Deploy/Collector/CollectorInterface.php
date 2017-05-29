<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Collector;

use Magento\Deploy\Package\Package;

/**
 * Interface CollectorInterface
 *
 * Collector returns packages with files which share same properties (e.g. area, theme, locale, etc)
 */
interface CollectorInterface
{
    /**
     * Retrieve all static files from registered locations split to packages.
     * Unique package is identified for each combination of three key scope identifiers:
     * - area
     * - theme
     * - locale
     *
     * @return Package[]
     */
    public function collect();
}
