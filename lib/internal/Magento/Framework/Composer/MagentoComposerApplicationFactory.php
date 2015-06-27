<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\App\Filesystem\DirectoryList;

class MagentoComposerApplicationFactory
{

    /**
     * @var string
     */
    private $pathToComposerHome;

    /**
     * @var string
     */
    private $pathToComposerJson;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @throws \Exception
     */
    public function __construct(DirectoryList $directoryList)
    {
        // composer.json is in same directory as vendor
        $vendorPath = $directoryList->getPath(DirectoryList::CONFIG) . '/vendor_path.php';
        $vendorDir = require "{$vendorPath}";

        $composerJson = $directoryList->getPath(DirectoryList::ROOT) . "/{$vendorDir}/../composer.json";

        $this->pathToComposerJson = realpath($composerJson);

        $this->pathToComposerHome = $directoryList->getPath(DirectoryList::COMPOSER_HOME);

        if ($this->pathToComposerJson === false) {
            throw new \Exception('Composer file not found: ' . $composerJson);
        }
    }

    /**
     * Creates MagentoComposerApplication instance
     *
     * @return MagentoComposerApplication
     */
    public function create()
    {
        return new MagentoComposerApplication($this->pathToComposerHome, $this->pathToComposerJson);
    }
}
