<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Composer\MagentoComposerApplication;

class MagentoComposerApplicationFactory
{
    /**
     * Creates MagentoComposerApplication instance
     *
     * @param string $pathToComposerHome
     * @param string  $pathToComposerJson
     *
     * @return MagentoComposerApplication
     */
    public function create($pathToComposerHome, $pathToComposerJson)
    {
        return new MagentoComposerApplication($pathToComposerHome, $pathToComposerJson);
    }
}
