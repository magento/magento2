<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Composer\Package\Version\VersionParser;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking if DB version is in sync with the code base version
 * @since 2.0.0
 */
class DbStatusCommand extends AbstractSetupCommand
{
    /**
     * Code for error when application upgrade is required.
     */
    const EXIT_CODE_UPGRADE_REQUIRED = 2;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     * @since 2.0.0
     */
    private $objectManagerProvider;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     * @since 2.0.0
     */
    private $deploymentConfig;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DeploymentConfig $deploymentConfig
     * @since 2.0.0
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, DeploymentConfig $deploymentConfig)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('setup:db:status')
            ->setDescription('Checks if DB schema or data requires upgrade');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                "<info>No information is available: the Magento application is not installed.</info>"
            );
            return Cli::RETURN_FAILURE;
        }
        /** @var DbVersionInfo $dbVersionInfo */
        $dbVersionInfo = $this->objectManagerProvider->get()
            ->get(\Magento\Framework\Module\DbVersionInfo::class);
        $outdated = $dbVersionInfo->getDbVersionErrors();
        if (!empty($outdated)) {
            $output->writeln("<info>The module code base doesn't match the DB schema and data.</info>");
            $versionParser = new VersionParser();
            $codebaseUpdateNeeded = false;
            foreach ($outdated as $row) {
                if (!$codebaseUpdateNeeded && $row[DbVersionInfo::KEY_CURRENT] !== 'none') {
                    // check if module code base update is needed
                    $currentVersion = $versionParser->parseConstraints($row[DbVersionInfo::KEY_CURRENT]);
                    $requiredVersion = $versionParser->parseConstraints('>' . $row[DbVersionInfo::KEY_REQUIRED]);
                    if ($requiredVersion->matches($currentVersion)) {
                        $codebaseUpdateNeeded = true;
                    };
                }
                $output->writeln(sprintf(
                    "<info>%20s %10s: %11s  ->  %-11s</info>",
                    $row[DbVersionInfo::KEY_MODULE],
                    $row[DbVersionInfo::KEY_TYPE],
                    $row[DbVersionInfo::KEY_CURRENT],
                    $row[DbVersionInfo::KEY_REQUIRED]
                ));
            }
            if ($codebaseUpdateNeeded) {
                $output->writeln(
                    '<info>Some modules use code versions newer or older than the database. ' .
                    "First update the module code, then run 'setup:upgrade'.</info>"
                );
                return Cli::RETURN_FAILURE;
            }

            $output->writeln(
                "<info>Run 'setup:upgrade' to update your DB schema and data.</info>"
            );
            return static::EXIT_CODE_UPGRADE_REQUIRED;
        }

        $output->writeln(
            '<info>All modules are up to date.</info>'
        );
        return Cli::RETURN_SUCCESS;
    }
}
