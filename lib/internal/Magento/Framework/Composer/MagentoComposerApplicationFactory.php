<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Composer\InfoCommand;
use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\RequireUpdateDryRunCommand;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Framework\Composer\MagentoComposerApplicationFactory
 *
 * @since 2.0.0
 */
class MagentoComposerApplicationFactory
{

    /**
     * @var string
     * @since 2.0.0
     */
    private $pathToComposerHome;

    /**
     * @var string
     * @since 2.0.0
     */
    private $pathToComposerJson;

    /**
     * Constructor
     *
     * @param ComposerJsonFinder $composerJsonFinder
     * @param DirectoryList $directoryList
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create()
    {
        return new MagentoComposerApplication($this->pathToComposerHome, $this->pathToComposerJson);
    }

    /**
     * Creates InfoCommand instance
     *
     * @return InfoCommand
     * @since 2.0.0
     */
    public function createInfoCommand()
    {
        return new InfoCommand($this->create());
    }

    /**
     * Creates RequireUpdateDryRunCommand instance
     *
     * @return RequireUpdateDryRunCommand
     * @since 2.0.0
     */
    public function createRequireUpdateDryRunCommand()
    {
        return new RequireUpdateDryRunCommand($this->create(), $this->createInfoCommand());
    }
}
