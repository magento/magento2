<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Console\Command;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to set Admin Adobe IMS Module mode
 */
class AdminAdobeImsInfoCommand extends Command
{
    /**
     * Human-readable name for Organization ID input option
     */
    private const ORGANIZATION_ID_NAME = 'Organization ID';

    /**
     * Human-readable name for Client ID input option
     */
    private const CLIENT_ID_NAME = 'Client ID';

    /**
     * Human-readable name for Client Secret input option
     */
    private const CLIENT_SECRET_NAME = 'Client Secret';

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $adminImsConnection;

    /**
     * @param ImsConfig $adminImsConfig
     * @param ImsConnection $adminImsConnection
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        ImsConnection $adminImsConnection
    ) {
        parent::__construct();
        $this->adminImsConfig = $adminImsConfig;
        $this->adminImsConnection = $adminImsConnection;

        $this->setName('admin:adobe-ims:info')
            ->setDescription('Information of Adobe IMS Module configuration');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            if ($this->adminImsConfig->enabled()) {
                $clientId = $this->adminImsConfig->getApiKey();
                if ($this->adminImsConnection->testAuth($clientId)) {
                    $clientSecret = $this->adminImsConfig->getPrivateKey() ? 'configured' : 'not configured';
                    $output->writeln(self::CLIENT_ID_NAME . ': ' . $clientId);
                    $output->writeln(self::ORGANIZATION_ID_NAME . ': ' . $this->adminImsConfig->getOrganizationId());
                    $output->writeln(self::CLIENT_SECRET_NAME . ' ' . $clientSecret);
                }
            } else {
                $output->writeln(__('Module is disabled'));
            }

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return Cli::RETURN_FAILURE;
        }
    }
}
