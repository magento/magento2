<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks if config.xml is configured properly.
 */
class ConfigAnalyzer extends \Symfony\Component\Console\Command\Command
{
    /**
     * Config file path.
     *
     * @var string
     */
    private $configFilePath = MTF_BP . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'config.xml';

    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Config.xml file data.
     *
     * @var DataInterface
     */
    private $configXml;

    /**
     * Config.xml.dist file data.
     *
     * @var DataInterface
     */
    private $configXmlDist;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DataInterface $configXml
     * @param DataInterface $configXmlDist
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DataInterface $configXml,
        DataInterface $configXmlDist
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->configXml = $configXml->get();
        $this->configXmlDist = $configXmlDist->get();
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:check-config-valid')
            ->setDescription('Check if config.xml is configured properly.');
    }

    /**
     * Execute check if config.xml is configured properly.
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
        $output->writeln("Checking config.xml file configuration...");
        $output->outputMessages($this->checkConfigFileAvailable());
        $output->writeln("config.xml file check is finished.");
    }

    /**
     * Check if config.xml file is present in MTF_BP/etc folder.
     *
     * @return array
     */
    private function checkConfigFileAvailable()
    {
        $messages = [];
        $configFileExists = false;
        if (file_exists($this->configFilePath)) {
            $configFileExists = true;
            if ($this->recursiveKeys($this->configXml) != $this->recursiveKeys($this->configXmlDist)) {
                $messages['error'][] = 'Check your config.xml file to contain all configs from config.xml.dist.';
            }
        } else {
            if (file_exists($this->configFilePath . '.dist')) {
                if (!copy($this->configFilePath . '.dist', $this->configFilePath)) {
                    $messages['error'][] = 'Failed to copy config.xml.dist to config.xml.';
                    return $messages;
                }
                $messages['info'][] = 'config.xml file has been created based on config.xml.dist.';
                $configFileExists = true;
            }
        }
        if (!$configFileExists) {
            $messages['error'][] = 'Cannot define config.xml configuration path.';
        }
        return $messages;
    }

    /**
     * Get array of array keys.
     *
     * @param array $input
     * @return array
     */
    private function recursiveKeys(array $input)
    {
        $output = array_keys($input);
        foreach ($input as $sub) {
            if (is_array($sub)) {
                $output = array_merge($output, $this->recursiveKeys($sub));
            }
        }
        return $output;
    }
}
