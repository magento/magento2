<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PHPUnit analyzer based on the URL specified in the phpunit.xml.
 */
class PhpUnitAnalyzer extends \Symfony\Component\Console\Command\Command
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
        $this->setName('troubleshooting:check-phpunit-config-file')
            ->setDescription('Check if phpunit file is available.');
    }

    /**
     * Execute command for checkout phpunit.xml config file availability.
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
        $output->writeln("Checking phpunit.xml file availability...");
        $messages = [];
        $configFileExists = false;
        if (file_exists(MTF_PHPUNIT_FILE)) {
            $configFileExists = true;
        } else {
            if (file_exists(MTF_PHPUNIT_FILE . '.dist')) {
                if (!copy(MTF_PHPUNIT_FILE . '.dist', MTF_PHPUNIT_FILE)) {
                    $messages['error'][] = 'Failed to copy phpunit.xml.dist to phpunit.xml.';
                } else {
                    $messages['info'][] = 'phpunit.xml file has been created based on phpunit.xml.dist. '
                        . 'Please, adjust your PHPUnit configuration to use new file.';
                    $configFileExists = true;
                }
            }
        }
        if (!$configFileExists) {
            $messages['error'][] = 'Cannot define phpunit configuration path.';
        }
        $output->outputMessages($messages);
        $output->writeln("phpunit.xml check finished.");
    }
}
