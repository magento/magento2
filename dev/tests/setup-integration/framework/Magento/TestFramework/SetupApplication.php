<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework;

/**
 * Encapsulates application installation, initialization and uninstall, add flag to skip database dump.
 *
 * Allow installation and uninstallation.
 */
class SetupApplication extends Application
{
    /**
     * {@inheritdoc}
     */
    protected $dumpDb = false;

    /**
     * @var bool
     */
    protected $canLoadArea = false;

    /**
     * @var bool
     */
    protected $canInstallSequence = false;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        throw new \Exception("Can't start application.");
    }
}
