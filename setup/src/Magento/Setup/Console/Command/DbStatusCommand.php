<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Composer\Package\Version\VersionParser;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking if DB version is in sync with the code base version
 */
class DbStatusCommand extends AbstractSetupCommand
{
    /**
     * Object manager provider
     *
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
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, DeploymentConfig $deploymentConfig)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:db:status')
            ->setDescription('Checks if DB schema or data requires upgrade');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln("<info>No information is available: the Magento application is not installed.</info>");
            return;
        }
        /** @var DbVersionInfo $dbVersionInfo */
        $dbVersionInfo = $this->objectManagerProvider->get()
            ->get('Magento\Framework\Module\DbVersionInfo');
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
                // we must have an exit code higher than zero to indicate something was wrong
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            } else {
                $output->writeln("<info>Run 'setup:upgrade' to update your DB schema and data.</info>");
            }
        } else {
            $output->writeln('<info>All modules are up to date.</info>');
        }
    }
}
