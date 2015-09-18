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
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
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
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}