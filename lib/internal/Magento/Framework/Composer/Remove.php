<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Composer\Console\Application;
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
     * @param Application $composerApp
     * @param DirectoryList $directoryList
     */
    public function __construct(Application $composerApp, DirectoryList $directoryList)
    {
        $this->composerApp = $composerApp;
        $this->directoryList = $directoryList;
    }

    /**
     * Run 'composer remove'
     *
     * @param array $packages
     * @return void
     */
    public function remove(array $packages)
    {
        $this->composerApp->resetComposer();
        $this->composerApp->setAutoExit(false);
        $vendor = include $this->directoryList->getPath(DirectoryList::CONFIG) . '/vendor_path.php';
        $this->composerApp->run(
            new ArrayInput(
                [
                    'command' => 'remove',
                    'packages' => $packages,
                    '--working-dir' => $this->directoryList->getRoot() . '/' . $vendor . '/..'
                ]
            )
        );
    }
}
