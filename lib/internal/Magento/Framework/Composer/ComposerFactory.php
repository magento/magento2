<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Composer\IO\BufferIO;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Framework\Composer\ComposerFactory
 *
 * @since 2.1.0
 */
class ComposerFactory
{
    /**
     * @var DirectoryList
     * @since 2.1.0
     */
    private $directoryList;

    /**
     * @var ComposerJsonFinder
     * @since 2.1.0
     */
    private $composerJsonFinder;

    /**
     * @param DirectoryList $directoryList
     * @param ComposerJsonFinder $composerJsonFinder
     * @since 2.1.0
     */
    public function __construct(
        DirectoryList $directoryList,
        ComposerJsonFinder $composerJsonFinder
    ) {
        $this->directoryList = $directoryList;
        $this->composerJsonFinder = $composerJsonFinder;
    }

    /**
     * Create \Composer\Composer
     *
     * @return \Composer\Composer
     * @throws \Exception
     * @since 2.1.0
     */
    public function create()
    {
        putenv('COMPOSER_HOME=' . $this->directoryList->getPath(DirectoryList::COMPOSER_HOME));

        return \Composer\Factory::create(
            new BufferIO(),
            $this->composerJsonFinder->findComposerJson()
        );
    }
}
