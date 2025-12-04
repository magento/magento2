<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\NewRelicReporting\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;
use Magento\NewRelicReporting\Model\ServiceShellUser;

class DeployMarker extends Command
{
    /**
     * @var DeploymentsFactory
     */
    private DeploymentsFactory $deploymentsFactory;

    /**
     * @var ServiceShellUser
     */
    private ServiceShellUser $serviceShellUser;
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Initialize dependencies.
     *
     * @param DeploymentsFactory $deploymentsFactory
     * @param ServiceShellUser $serviceShellUser
     * @param Config $config
     * @param string|null $name
     */
    public function __construct(
        DeploymentsFactory $deploymentsFactory,
        ServiceShellUser $serviceShellUser,
        Config $config,
        ?string $name = null
    ) {
        $this->deploymentsFactory = $deploymentsFactory;
        $this->serviceShellUser = $serviceShellUser;
        $this->config = $config;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("newrelic:create:deploy-marker");
        $this->setDescription("Create a deployment marker in New Relic (supports both v2 REST and NerdGraph)")
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Deploy Message / Description'
            )
            ->addArgument(
                'change_log',
                InputArgument::REQUIRED,
                'Change Log?'
            )
            ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'Deployment User'
            )->addArgument(
                'revision',
                InputArgument::OPTIONAL,
                'Revision / Version'
            )
            ->addOption(
                'commit',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Git commit hash for this deployment (NerdGraph only)'
            )
            ->addOption(
                'deep-link',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Deep link to deployment details (NerdGraph only)'
            )
            ->addOption(
                'group-id',
                'g',
                InputOption::VALUE_OPTIONAL,
                'Group ID for organizing deployments (NerdGraph only)'
            );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isEnabled = $this->config->isNewRelicEnabled();
        if (!$isEnabled) {
            $output->writeln('<error>✗ New Relic is not enabled. Please check your configuration.</error>');
            return Command::FAILURE;
        }
        try {
            $result = $this->deploymentsFactory->create()->setDeployment(
                $input->getArgument('message'),
                $input->getArgument('change_log') ?: false,
                $this->serviceShellUser->get($input->getArgument('user')) ?: false,
                $input->getArgument('revision'),
                $input->getOption('commit'),
                $input->getOption('deep-link'),
                $input->getOption('group-id')
            );

            if ($result !== false) {
                $output->writeln('<info>✓ NewRelic deployment marker created successfully!</info>');

                // Display enhanced details if available (from NerdGraph)
                if (is_array($result) && isset($result['deploymentId'])) {
                    $this->displayDeploymentDetails($output, $result);
                }

                return Command::SUCCESS;
            } else {
                $output->writeln('<error>✗ Failed to create deployment marker</error>');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>✗ Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    /**
     * Display deployment details from NerdGraph response
     *
     * @param OutputInterface $output
     * @param array $deployment
     */
    private function displayDeploymentDetails(OutputInterface $output, array $deployment): void
    {
        $output->writeln('');
        $output->writeln('<comment>Deployment Details:</comment>');

        $table = new Table($output);
        $table->setHeaders(['Field', 'Value']);

        $rows = [
            ['Deployment ID', $deployment['deploymentId'] ?? 'N/A'],
            ['Entity GUID', $deployment['entityGuid'] ?? 'N/A'],
            ['Version', $deployment['version'] ?? 'N/A'],
            ['Description', $deployment['description'] ?? 'N/A'],
            ['User', $deployment['user'] ?? 'N/A'],
            ['Timestamp', $deployment['timestamp'] ?
                date(
                    'Y-m-d H:i:s',
                    (int)($deployment['timestamp'] / 1000)
                ) : 'N/A']
        ];

        if (!empty($deployment['changelog'])) {
            $rows[] = ['Change log', $deployment['changelog']];
        }
        if (!empty($deployment['commit'])) {
            $rows[] = ['Commit', $deployment['commit']];
        }
        if (!empty($deployment['deepLink'])) {
            $rows[] = ['Deep Link', $deployment['deepLink']];
        }
        if (!empty($deployment['groupId'])) {
            $rows[] = ['Group ID', $deployment['groupId']];
        }

        $table->setRows($rows);
        $table->render();
    }
}
