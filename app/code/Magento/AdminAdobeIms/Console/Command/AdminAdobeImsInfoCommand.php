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
    private ImsConfig $imsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @param ImsConfig $imsConfig
     * @param ImsConnection $imsConnection
     */
    public function __construct(
        ImsConfig $imsConfig,
        ImsConnection $imsConnection
    ) {
        parent::__construct();
        $this->imsConfig = $imsConfig;
        $this->imsConnection = $imsConnection;

        $this->setName('admin:adobe-ims:info')
            ->setDescription('Information of Adobe IMS Module configuration');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            if ($this->imsConfig->enabled()) {
                $clientId = $this->imsConfig->getApiKey();
                if ($this->imsConnection->testAuth($clientId)) {
                    $output->writeln(self::CLIENT_ID_NAME . ': ' . $clientId);
                    $output->writeln(self::ORGANIZATION_ID_NAME . ': ' . $this->imsConfig->getOrganizationId());
                    $clientSecret = $this->imsConfig->getPrivateKey() ? 'configured' : 'not configured';
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
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }
    }
}
