<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * A class to find path to root Composer json file
 * @since 2.0.0
 */
class ComposerJsonFinder
{
    /**
     * @var DirectoryList $directoryList
     * @since 2.0.0
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @since 2.0.0
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Find absolute path to root Composer json file
     *
     * @return string
     * @throws \Exception
     * @since 2.0.0
     */
    public function findComposerJson()
    {
        $composerJson = $this->directoryList->getPath(DirectoryList::ROOT) . '/composer.json';

        $composerJson = realpath($composerJson);

        if ($composerJson === false) {
            throw new \Exception('Composer file not found');
        }

        return $composerJson;
    }
}
