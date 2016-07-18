<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Composer\IO\BufferIO;
use Magento\Framework\App\Filesystem\DirectoryList;

class ComposerFactory
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ComposerJsonFinder
     */
    private $composerJsonFinder;

    /**
     * @param DirectoryList $directoryList
     * @param ComposerJsonFinder $composerJsonFinder
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
