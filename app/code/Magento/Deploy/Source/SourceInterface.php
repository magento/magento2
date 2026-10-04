<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Source;

use Magento\Deploy\Package\PackageFile;

/**
 * Provide list of files located in some source location (e.g. modules directories, library, themes, else)
 *
 * @api
 */
interface SourceInterface
{
    /**
     * Return the list of files located in source
     *
     * @return PackageFile[]
     */
    public function get();
}
