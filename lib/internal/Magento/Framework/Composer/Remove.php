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
     * Composer application
     *
     * @var Application
     */
    private $composerApp;

    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param MagentoComposerApplication $composerApp
     * @param DirectoryList $directoryList
     */
    public function __construct(
        MagentoComposerApplication $composerApp,
        DirectoryList $directoryList
    ) {
        $this->composerApp = $composerApp;
        $this->directoryList = $directoryList;
    }

    /**
     * Run 'composer remove'
     *
     * @param array $packages
     * @throws \Exception
     *
     * @return void
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

        $this->composerApp->setConfig($composerHomePath, $composerJson);

        return $this->composerApp->runComposerCommand(
            [
                'command' => 'remove',
                'packages' => $packages
            ]
        );
    }
}
