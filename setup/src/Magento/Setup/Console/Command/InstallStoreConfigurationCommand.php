<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\ConsoleLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\StoreConfigurationDataMapper;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Store\Model\Store;

class InstallStoreConfigurationCommand extends AbstractSetupCommand
{
    /**
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Inject dependencies
     *
     * @param InstallerFactory $installerFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        InstallerFactory $installerFactory,
        DeploymentConfig $deploymentConfig,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->installerFactory = $installerFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->objectManager = $objectManagerProvider->get();
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:store-config:set')
            ->setDescription('Installs store configuration')
            ->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                "<info>Store settings can't be saved because the Magento application is not installed.</info>"
            );
            return;
        }
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln($errors);
            return;
        }
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->installUserConfig($input->getOptions());
    }

    /**
     * Get list of options for the command
     *
     * @return InputOption[]
     */
    public function getOptionsList()
    {
        return [
            new InputOption(
                StoreConfigurationDataMapper::KEY_BASE_URL,
                null,
                InputOption::VALUE_REQUIRED,
                'URL the store is supposed to be available at'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_LANGUAGE,
                null,
                InputOption::VALUE_REQUIRED,
                'Default language code'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_TIMEZONE,
                null,
                InputOption::VALUE_REQUIRED,
                'Default time zone code'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_CURRENCY,
                null,
                InputOption::VALUE_REQUIRED,
                'Default currency code'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_USE_SEF_URL,
                null,
                InputOption::VALUE_REQUIRED,
                'Use rewrites'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_IS_SECURE,
                null,
                InputOption::VALUE_REQUIRED,
                'Use secure URLs. Enable this option only if SSL is available.'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_BASE_URL_SECURE,
                null,
                InputOption::VALUE_REQUIRED,
                'Base URL for SSL connection'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN,
                null,
                InputOption::VALUE_REQUIRED,
                'Run admin interface with SSL'
            ),
            new InputOption(
                StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY,
                null,
                InputOption::VALUE_REQUIRED,
                'Whether to use a "security key" feature in Magento Admin URLs and forms'
            ),
        ];
    }

    /**
     * Check if option values provided by the user are valid
     *
     * @param InputInterface $input
     * @return string[]
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $options = $input->getOptions();
        foreach ($options as $key => $value) {
            if (!$value) {
                continue;
            }
            /** @var \Magento\Setup\Model\Lists $lists */
            $lists = $this->objectManager->get('Magento\Setup\Model\Lists');
            switch ($key) {
                case StoreConfigurationDataMapper::KEY_BASE_URL:
                    try {
                        $this->validateURL($value, Store::XML_PATH_UNSECURE_BASE_URL);
                    } catch (LocalizedException $e) {
                        $errors[] = '<error>' . 'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL
                            . '\': ' . $e->getLogMessage() .'</error>';
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_LANGUAGE:
                    if (!in_array($value, array_keys($lists->getLocaleList()))) {
                        $errors[] = '<error>' . 'Command option \'' . StoreConfigurationDataMapper::KEY_LANGUAGE
                            . '\': Invalid value. To see possible values, run command ' .
                            "'bin/magento info:language:list'.</error>";
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_TIMEZONE:
                    if (!in_array($value, array_keys($lists->getTimezoneList()))) {
                        $errors[] = '<error>' . 'Command option \'' . StoreConfigurationDataMapper::KEY_TIMEZONE
                            . '\': Invalid value. To see possible values, run command ' .
                            "'bin/magento info:timezone:list'.</error>";
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_CURRENCY:
                    if (!in_array($value, array_keys($lists->getCurrencyList()))) {
                        $errors[] = '<error>' . 'Command option \'' . StoreConfigurationDataMapper::KEY_CURRENCY
                            . '\': Invalid value. To see possible values, run command ' .
                            "'bin/magento info:currency:list'.</error>";
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_USE_SEF_URL:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_USE_SEF_URL);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                    break;
                case StoreConfigurationDataMapper::KEY_IS_SECURE:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_IS_SECURE);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_BASE_URL_SECURE:
                    try {
                        $this->validateURL($value, Store::XML_PATH_SECURE_BASE_URL);
                        if (strpos($value,'https:') === false) {
                            throw new LocalizedException(new \Magento\Framework\Phrase("Invalid secure URL."));
                        }
                    } catch (LocalizedException $e) {
                        $errors[] = '<error>' . 'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL_SECURE
                            . '\': ' . $e->getLogMessage() .'</error>';
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY:
                    $errorMsg = $this->validateBinaryValue(
                        $value,
                        StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY
                    );
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
            }
        }
        return $errors;
    }

    /**
     * Validate a URL
     *
     * @param string $value
     * @param string $path
     * @return void
     * @throws LocalizedException
     */
    private function validateURL($value, $path)
    {
        /** @var \Magento\Config\Model\Config\Backend\Baseurl $baseUrl */
        $baseUrl = $this->objectManager->get('Magento\Config\Model\Config\Backend\Baseurl');
        $baseUrl->setPath($path);
        $baseUrl->setValue($value);
        $baseUrl->beforeSave();
    }

    /**
     * Validate binary value for a specified key
     *
     * @param string $value
     * @param string $key
     * @return string
     */
    private function validateBinaryValue($value, $key)
    {
        $errorMsg = '';
        if ($value !== '0' && $value !== '1') {
            $errorMsg = '<error>' . 'Command option \'' . $key . '\': Invalid value. Possible values (0|1).</error>';
        }
        return $errorMsg;
    }
}
