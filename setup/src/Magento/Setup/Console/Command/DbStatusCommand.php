<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use Magento\Framework\Setup\Declaration\Schema\UpToDateDeclarativeSchema;
use Magento\Framework\Setup\OldDbValidator;
use Magento\Framework\Setup\Patch\UpToDateData;
use Magento\Framework\Setup\Patch\UpToDateSchema;
use Magento\Framework\Setup\UpToDateValidatorInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking if DB version is in sync with the code base version
 */
class DbStatusCommand extends AbstractSetupCommand
{
    /**
     * Code for error when application upgrade is required.
     */
    public const EXIT_CODE_UPGRADE_REQUIRED = 2;
    public const NAME = 'setup:db:status';

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var UpToDateValidatorInterface[]
     */
    private $upToDateValidators = [];

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, DeploymentConfig $deploymentConfig)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->deploymentConfig = $deploymentConfig;
        /**
         * As DbStatucCommand is in setup and all validators are part of the framework, we can`t configure
         * this command with dependency injection and we need to inject each validator manually
         */
        $this->upToDateValidators = [
            $this->objectManagerProvider->get()->get(UpToDateDeclarativeSchema::class),
            $this->objectManagerProvider->get()->get(UpToDateSchema::class),
            $this->objectManagerProvider->get()->get(UpToDateData::class),
            $this->objectManagerProvider->get()->get(OldDbValidator::class),
        ];
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Checks if DB schema or data requires upgrade');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timestamp = date('Y-m-d H:i:s');
        $output->writeln("<info>DbStatusCommand execution started at {$timestamp}</info>");

        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                "<info>No information is available: the Magento application is not installed.</info>"
            );
            return Cli::RETURN_FAILURE;
        }

        $outDated = false;

        foreach ($this->upToDateValidators as $validator) {
            $validatorClass = get_class($validator);

            try {
                $isUpToDate = $validator->isUpToDate();
                $output->writeln(
                    "<info>Validator {$validatorClass} isUpToDate: " . ($isUpToDate ? 'true' : 'false') . "</info>"
                );

                if (!$isUpToDate) {
                    $message = $validator->getNotUpToDateMessage();
                    $output->writeln(sprintf('<info>%s</info>', $message));

                    $details = $validator->getDetails();
                    if (!empty($details)) {
                        $detailsJson = json_encode($details, JSON_PRETTY_PRINT);
                        $output->writeln(sprintf('<info>Details: %s</info>', $detailsJson));
                    }

                    $outDated = true;
                }
            } catch (\Throwable $e) {
                $output->writeln(
                    "<info>Validator {$validatorClass} failed with error: " . $e->getMessage() . "</info>"
                );
                $output->writeln("<info>Treating as upgrade required due to validation error.</info>");
                $outDated = true;
            }
        }

        if ($outDated) {
            $output->writeln('<info>Run \'setup:upgrade\' to update your DB schema and data.</info>');
            return self::EXIT_CODE_UPGRADE_REQUIRED;
        }

        $output->writeln(
            '<info>All modules are up to date.</info>'
        );
        return Cli::RETURN_SUCCESS;
    }
}
