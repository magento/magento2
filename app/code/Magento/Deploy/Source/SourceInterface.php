<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

use Magento\Deploy\Package\PackageFile;

/**
 * Provide list of files located in some source location (e.g. modules directories, library, themes, else)
 * @since 2.2.0
 */
interface SourceInterface
{
    /**
     * Return the list of files located in source
     *
     * @return PackageFile[]
     * @since 2.2.0
     */
    public function get();
}
