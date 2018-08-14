<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to set application mode
 */
class SetModeCommand extends Command
{
    /**
     * Name of "target application mode" input argument
     */
    const MODE_ARGUMENT = 'mode';

    /**
     * Name of "skip compilation" input option
     */
    const SKIP_COMPILATION_OPTION = 'skip-compilation';

    /**
     * Object manager factory
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $description = 'Set application mode.';

        $this->setName('deploy:mode:set')
            ->setDescription($description)
            ->setDefinition([
                new InputArgument(
                    self::MODE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'The application mode to set. Available options are "developer" or "production"'
                ),
                new InputOption(
                    self::SKIP_COMPILATION_OPTION,
                    's',
                    InputOption::VALUE_NONE,
                    'Skips the clearing and regeneration of static content (generated code, preprocessed CSS, '
                    . 'and assets in pub/static/)'
                )
            ]);
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var \Magento\Deploy\Model\Mode $modeController */
            $modeController = $this->objectManager->create(
                \Magento\Deploy\Model\Mode::class,
                [
                    'input' => $input,
                    'output' => $output,
                ]
            );
            $toMode = $input->getArgument(self::MODE_ARGUMENT);
            $skipCompilation = $input->getOption(self::SKIP_COMPILATION_OPTION);
            switch ($toMode) {
                case State::MODE_DEVELOPER:
                    $modeController->enableDeveloperMode();
                    break;
                case State::MODE_PRODUCTION:
                    if ($skipCompilation) {
                        $modeController->enableProductionModeMinimal();
                    } else {
                        $modeController->enableProductionMode();
                    }
                    break;
                case State::MODE_DEFAULT:
                    $modeController->enableDefaultMode();
                    break;
                default:
                    throw new LocalizedException(__('Cannot switch into given mode "%1"', $toMode));
            }
            $output->writeln('Enabled ' . $toMode . ' mode.');

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
