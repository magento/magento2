<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying status of modules
 * @since 2.0.0
 */
class ModuleStatusCommand extends AbstractSetupCommand
{
    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     * @since 2.0.0
     */
    private $objectManagerProvider;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('module:status')
            ->setDescription('Displays status of modules');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleList = $this->objectManagerProvider->get()->create(\Magento\Framework\Module\ModuleList::class);
        $output->writeln('<info>List of enabled modules:</info>');
        $enabledModules = $moduleList->getNames();
        if (count($enabledModules) === 0) {
            $output->writeln('None');
        } else {
            $output->writeln(join("\n", $enabledModules));
        }
        $output->writeln('');

        $fullModuleList = $this->objectManagerProvider->get()->create(\Magento\Framework\Module\FullModuleList::class);
        $output->writeln("<info>List of disabled modules:</info>");
        $disabledModules = array_diff($fullModuleList->getNames(), $enabledModules);
        if (count($disabledModules) === 0) {
            $output->writeln('None');
        } else {
            $output->writeln(join("\n", $disabledModules));
        }
    }
}
