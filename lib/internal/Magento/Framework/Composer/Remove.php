<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Magento\Composer\MagentoComposerApplication;

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
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerApplicationFactory
     */
    public function __construct(
        MagentoComposerApplicationFactory $composerApplicationFactory
    ) {
        $this->composerApplicationFactory = $composerApplicationFactory;
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
        $composerApplication = $this->composerApplicationFactory->create();

        return $composerApplication->runComposerCommand(
            [
                'command' => 'remove',
                'packages' => $packages,
                '--no-update' => true,
            ]
        );
    }
}
