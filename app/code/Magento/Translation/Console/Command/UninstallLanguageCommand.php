<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Composer\GeneralDependencyChecker;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\App\Cache;

/**
 * Class UninstallLanguageCommand
 */
class UninstallLanguageCommand extends Command
{
    /**
     * Language code argument name
     */
    const PACKAGE_ARGUMENT = 'package';

    /**
     * @var GeneralDependencyChecker
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
     * Inject dependencies
     *
     * @param GeneralDependencyChecker $dependencyChecker
     * @param Remove $remove
     * @param ComposerInformation $composerInfo
     * @param Cache $cache
     */
    public function __construct(
        GeneralDependencyChecker $dependencyChecker,
        Remove $remove,
        ComposerInformation $composerInfo,
        Cache $cache
    ) {
        $this->dependencyChecker = $dependencyChecker;
        $this->remove = $remove;
        $this->composerInfo = $composerInfo;
        $this->cache = $cache;

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
                    'Language code'
                )
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

        $dependencies = $this->dependencyChecker->checkDependencies($languages);

        foreach ($languages as $package) {

            if (!$this->validate($package)) {
                $output->writeln("<info>Package $package is not magento language and will be skipped.<info>");
            } else {
                if (sizeof($dependencies[$package]) > 0) {
                    $output->writeln("<info>Package $package has dependencies and will be skipped.<info>");
                } else {
                    $packagesToRemove[] = $package;
                }
            }
        }

        if ($packagesToRemove !== []) {
            $this->remove->remove($packagesToRemove);
            $this->cache->clean();
        } else {
            $output->writeln('Nothing is removed.');
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
        $installedPackages = $this->composerInfo->getRootRequiredPackagesAndTypes();

        if (isset($installedPackages[$package]) && $installedPackages[$package] === 'magento2-language') {
            return true;
        }

        return false;
    }
}
