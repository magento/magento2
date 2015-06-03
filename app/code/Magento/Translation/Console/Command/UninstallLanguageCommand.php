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
use Magento\Framework\Validator\Locale;
use Magento\Framework\Composer\GeneralDependencyChecker;
use Magento\Framework\Composer\Remove;

/**
 * Class UninstallLanguageCommand
 */
class UninstallLanguageCommand extends Command
{
    /**
     * Language code argument name
     */
    const CODE_ARGUMENT = 'code';

    /**
     * @var Locale
     */
    private $validator;

    /**
     * @var GeneralDependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var Remove
     */
    private $remove;

    /**
     * Inject dependencies
     *
     * @param Locale $validator
     * @param GeneralDependencyChecker $dependencyChecker
     * @param Remove $remove
     */
    public function __construct(
        Locale $validator,
        GeneralDependencyChecker $dependencyChecker,
        Remove $remove
    ) {
        $this->validator = $validator;
        $this->dependencyChecker = $dependencyChecker;
        $this->remove = $remove;

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
                    self::CODE_ARGUMENT,
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
        $languages = $input->getArgument(self::CODE_ARGUMENT);
        $packagesToRemove = [];

        $dependencies = $this->dependencyChecker->checkDependencies($languages);

        foreach ($languages as $package) {

            //TODO: validation

            if (sizeof($dependencies[$package]) > 0) {
                $output->writeln("<info>Package $package has dependencies and will be skipped.<info>");
            } else {
                $packagesToRemove[] = $package;
            }

            if ($packagesToRemove !== []) {
                $this->remove->remove($packagesToRemove);
            } else {
                $output->writeln('Nothing is removed.');
            }
        }
    }
}
