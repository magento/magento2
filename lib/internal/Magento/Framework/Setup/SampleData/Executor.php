<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Class \Magento\Framework\Setup\SampleData\Executor
 *
 * @since 2.0.0
 */
class Executor
{
    /**
     * @var State
     * @since 2.0.0
     */
    private $state;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    private $appState;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param State $state
     * @param \Magento\Framework\App\State $appState
     * @since 2.0.0
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
     * Catch exception if it appeared and continue installation
     *
     * @param InstallerInterface $installer
     * @return void
     * @since 2.0.0
     */
    public function exec(InstallerInterface $installer)
    {
        try {
            $this->appState->emulateAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL, [$installer, 'install']);
            $this->state->setInstalled();
        } catch (\Exception $e) {
            $this->state->setError();
            $this->logger->error('Sample Data error: ' . $e->getMessage());
        }
    }
}
