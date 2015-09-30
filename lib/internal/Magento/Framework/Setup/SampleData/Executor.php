<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

class Executor
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param StateInterface $state
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        StateInterface $state
    ) {
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * Execute SampleData module installation.
     * Catch exception if it appeared and continue installation
     *
     * @param InstallerInterface $installer
     * @return void
     */
    public function exec(InstallerInterface $installer)
    {
        try {
            $installer->install();
            $this->state->setInstalled();
        } catch (\Exception $e) {
            $this->state->setError();
            $this->logger->error('Sample Data error: ' . $e->getMessage());
        }
    }
}
