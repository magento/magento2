<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Console\Command;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to set Admin Adobe IMS Module mode
 */
class AdminAdobeImsStatusCommand extends Command
{
    private const MODE_ENABLE = 'enable';
    private const MODE_DISABLE = 'disable';

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConfig $adminImsConfig
    ) {
        parent::__construct();
        $this->adminImsConfig = $adminImsConfig;

        $this->setName('admin:adobe-ims:status')
            ->setDescription('Status of Adobe IMS Module');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $status = $this->getModuleStatus();
            $output->writeln(__('Admin Adobe IMS integration is %1', $status));

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Get Admin Adobe IMS Module status
     *
     * @return string
     */
    private function getModuleStatus(): string
    {
        return $this->adminImsConfig->enabled() ? self::MODE_ENABLE .'d' : self::MODE_DISABLE.'d';
    }
}
