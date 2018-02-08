<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Composer\DependencyChecker;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\App\Cache;
use Magento\Framework\Setup\BackupRollbackFactory;

/**
 * Command for uninstalling language and backup-code feature
 * 
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UninstallLanguageCommand extends Command
{
    /**
     * Language code argument name
     */
    const PACKAGE_ARGUMENT = 'package';

    /**
     * Backup-code option name
     */
    const BACKUP_CODE_OPTION = 'backup-code';

    /**
     * @var DependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var Remove
     */
    private $remove;

    /**
     * @var ComposerInformation
     */
    private $composerInfo;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Inject dependencies
     *
     * @param DependencyChecker $dependencyChecker
     * @param Remove $remove
     * @param ComposerInformation $composerInfo
     * @param Cache $cache
     * @param BackupRollbackFactory $backupRollbackFactory
     */
    public function __construct(
        DependencyChecker $dependencyChecker,
        Remove $remove,
        ComposerInformation $composerInfo,
        Cache $cache,
        BackupRollbackFactory $backupRollbackFactory
    ) {
        $this->dependencyChecker = $dependencyChecker;
        $this->remove = $remove;
        $this->composerInfo = $composerInfo;
        $this->cache = $cache;
        $this->backupRollbackFactory = $backupRollbackFactory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('i18n:uninstall')
            ->setDescription('Uninstalls language packages')
            ->setDefinition([
                new InputArgument(
                    self::PACKAGE_ARGUMENT,
                    InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                    'Language package name'
                ),
                new InputOption(
                    self::BACKUP_CODE_OPTION,
                    '-b',
                    InputOption::VALUE_NONE,
                    'Take code and configuration files backup (excluding temporary files)'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = $input->getArgument(self::PACKAGE_ARGUMENT);
        $packagesToRemove = [];
        $dependencies = $this->dependencyChecker->checkDependencies($languages, true);

        foreach ($languages as $package) {
            if (!$this->validate($package)) {
                $output->writeln("<info>Package $package is not a Magento language and will be skipped.</info>");
            } else {
                if (sizeof($dependencies[$package]) > 0) {
                    $output->writeln("<info>Package $package has dependencies and will be skipped.</info>");
                } else {
                    $packagesToRemove[] = $package;
                }
            }
        }

        if ($packagesToRemove !== []) {
            if ($input->getOption(self::BACKUP_CODE_OPTION)) {
                $backupRestore = $this->backupRollbackFactory->create($output);
                $backupRestore->codeBackup(time());
            } else {
                $output->writeln('<info>You are removing language package without a code backup.</info>');
            }

            $output->writeln($this->remove->remove($packagesToRemove));
            $this->cache->clean();
        } else {
            $output->writeln('<info>Nothing is removed.</info>');
        }
    }

    /**
     * Validates user input
     *
     * @param string $package
     *
     * @return bool
     */
    private function validate($package)
    {
        $installedPackages = $this->composerInfo->getRootRequiredPackageTypesByName();

        if (isset($installedPackages[$package]) && $installedPackages[$package] === 'magento2-language') {
            return true;
        }

        return false;
    }
}
