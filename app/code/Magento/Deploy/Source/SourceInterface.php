<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

use Magento\Deploy\Package\PackageFile;

/**
 * Interface SourceInterface
 *
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
