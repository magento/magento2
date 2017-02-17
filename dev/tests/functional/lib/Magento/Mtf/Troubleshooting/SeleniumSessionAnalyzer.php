<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Mtf\Client\Driver\Selenium\Driver;

/**
 * Analyze if Selenium session connection is established.
 */
class SeleniumSessionAnalyzer extends \Symfony\Component\Console\Command\Command
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
        $this->setName('troubleshooting:check-selenium-session-connection')
            ->setDescription('Check that Selenium session connection is established.');
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
        $output->writeln("Verifying selenium server session...");
        try {
            $driver = $this->objectManager->create(Driver::class);
            $driver->closeWindow();
        } catch (\Exception $e) {
            $output->outputMessages(['error' =>
                [
                    'The Selenium Server session cannot be established. Check if:'
                    . PHP_EOL . "\tSelenium server is launched."
                    . PHP_EOL . "\tSelenium server host and port configuration are correct in etc/config.xml."
                    . PHP_EOL . "\tThere is a valid browser name in etc/config.xml."
                    . PHP_EOL . "\tSelenium server is run with appropriate driver."
                    . PHP_EOL . "\tSelenium server version is compatible with web browser version."
                ]
            ]);
        }
        $output->writeln('Verification of selenium server session is finished.');
    }
}
