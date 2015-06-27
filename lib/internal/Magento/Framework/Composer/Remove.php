<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class to run composer remove command
 */
class Remove
{
    /**
     * Composer application factory
     *
     * @var MagentoComposerApplicationFactory
     */
    private $composerApplicationFactory;

    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerApplicationFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        MagentoComposerApplicationFactory $composerApplicationFactory,
        DirectoryList $directoryList
    ) {
        $this->composerApplicationFactory = $composerApplicationFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * Run 'composer remove'
     *
     * @param array $packages
     * @throws \Exception
     *
     * @return string
     */
    public function remove(array $packages)
    {
        $vendorDir = include $this->directoryList->getPath(DirectoryList::CONFIG) . '/vendor_path.php';
        $composerJson = $this->directoryList->getPath(DirectoryList::ROOT) . "/{$vendorDir}/../composer.json";

        $composerHomePath = $this->directoryList->getPath(DirectoryList::COMPOSER_HOME);
        $composerJsonRealPath = realpath($composerJson);

        if ($composerJsonRealPath === false) {
            throw new \Exception('Composer file not found: ' . $composerJson);
        }

        $composerApplication = $this->composerApplicationFactory->create($composerHomePath, $composerJson);

        return $composerApplication->runComposerCommand(
            [
                'command' => 'remove',
                'packages' => $packages
            ]
        );
    }
}
