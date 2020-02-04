<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Performs sample data installations.
 */
class Executor
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param State $state
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Setup\SampleData\State $state,
        \Magento\Framework\App\State $appState
    ) {
        $this->logger = $logger;
        $this->state = $state;
        $this->appState = $appState;
    }

    /**
     * Execute SampleData module installation.
     *
     * Catch exception if it appeared and continue installation
     *
     * @param InstallerInterface $installer
     * @return void
     */
    public function exec(InstallerInterface $installer)
    {
        try {
            $this->appState->emulateAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL, [$installer, 'install']);
            $this->state->setInstalled();
        } catch (\Throwable $e) {
            $this->state->setError();
            $this->logger->error('Sample Data error: ' . $e->getMessage());
        }
    }
}
