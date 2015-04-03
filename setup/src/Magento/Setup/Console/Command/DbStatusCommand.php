<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Composer\Package\Version\VersionParser;
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
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:db:status')
            ->setDescription('Checks if update of DB schema or data is required');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
                    'First update the module code, then run the "Update" command.</info>'
                );
            } else {
                $output->writeln('<info>Run the "Update" command to update your DB schema and data</info>');
            }
        } else {
            $output->writeln('<info>All modules are up to date</info>');
        }
    }
}
