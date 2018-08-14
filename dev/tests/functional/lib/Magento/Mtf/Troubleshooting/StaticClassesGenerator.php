<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\Console\Output;
use Magento\Mtf\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates static classes (Blocks, Pages, Repositories etc.).
 */
class StaticClassesGenerator extends \Symfony\Component\Console\Command\Command
{
    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct();
        $this->objectManager = $objectManager;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:generate-static-classes')
            ->setDescription('Generate static classes (Blocks, Pages, Repositories etc.).');
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
            Output::class,
            ['output' => $output]
        );
        $output->writeln("Generating static classes...");
        exec('php ' . MTF_BP . DIRECTORY_SEPARATOR . 'utils' . DIRECTORY_SEPARATOR . 'generate.php', $error, $exitCode);
        if ($exitCode) {
            $output->outputMessages(['error' => $error]);
        }
        $output->writeln('Static classes generation is finished.');
    }
}
