<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command;

use Magento\TestFramework\Event\Magento;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;

/**
 * Command to show application mode
 */
class ShowModeCommand extends Command
{
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $description = 'Displays current application mode.';

        $this->setName('deploy:mode:show')->setDescription($description);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var \Magento\Deploy\Model\Mode $mode */
            $mode = $this->objectManager->create(
                'Magento\Deploy\Model\Mode',
                [
                    'input' => $input,
                    'output' => $output,
                ]
            );
            $currentMode = $mode->getMode() ?: State::MODE_DEFAULT;
            $output->writeln(
                "Current application mode: $currentMode. (Note: Environment variables may override this value.)"
            );
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return;
        }
    }
}
