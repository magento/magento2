<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\App\State\State1;
use Magento\Mtf\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Analyze Magento configuration.
 */
class Configuration extends \Symfony\Component\Console\Command\Command
{
    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Example Application State class.
     *
     * @var State1
     */
    private $state1;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param State1 $state1
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        State1 $state1
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->state1 = $state1;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:apply-magento-configuration')
            ->setDescription('Apply proper Magento configuration to run functional tests.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = $this->objectManager->create(
            \Magento\Mtf\Console\Output::class,
            ['output' => $output]
        );
        $output->writeln("Applying Magento configuration...");
        $this->state1->apply();
        $output->outputMessages(
            ['info' => ['Magento configuration was updated in order to run functional tests without errors '
                . '(disabled WYSIWYG, enabled admin account sharing etc.).']
            ]
        );
    }
}
