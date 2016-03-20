<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Composer\InfoCommand;
use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\RequireUpdateDryRunCommand;
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
     * @param ComposerJsonFinder $composerJsonFinder
     * @param DirectoryList $directoryList
     */
    public function __construct(ComposerJsonFinder $composerJsonFinder, DirectoryList $directoryList)
    {
        $this->pathToComposerJson = $composerJsonFinder->findComposerJson();
        $this->pathToComposerHome = $directoryList->getPath(DirectoryList::COMPOSER_HOME);
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

    /**
     * Creates InfoCommand instance
     *
     * @return InfoCommand
     */
    public function createInfoCommand()
    {
        return new InfoCommand($this->create());
    }

    /**
     * Creates RequireUpdateDryRunCommand instance
     *
     * @return RequireUpdateDryRunCommand
     */
    public function createRequireUpdateDryRunCommand()
    {
        return new RequireUpdateDryRunCommand($this->create(), $this->createInfoCommand());
    }
}
