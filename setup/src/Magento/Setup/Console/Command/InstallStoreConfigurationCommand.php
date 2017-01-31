<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\Setup\ConsoleLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\StoreConfigurationDataMapper;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Framework\Validator\Locale;
use Magento\Framework\Validator\Timezone;
use Magento\Framework\Validator\Currency;
use Magento\Framework\Url\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
            ->setDescription('Installs the store configuration')
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
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln($errors);
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $options = $input->getOptions();
        foreach ($options as $key => $value) {
            if (!$value) {
                continue;
            }
            switch ($key) {
                case StoreConfigurationDataMapper::KEY_BASE_URL:
                    /** @var Validator $url */
                    if (strcmp($value, '{{base_url}}') == 0) {
                        break;
                    }
                    $url = $this->objectManager->get('Magento\Framework\Url\Validator');
                    if (!$url->isValid($value)) {
                        $errorMsgs = $url->getMessages();
                        $errors[] = '<error>' . 'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL
                            . '\': ' . $errorMsgs[Validator::INVALID_URL] .'</error>';
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_LANGUAGE:
                    /** @var Locale $lists */
                    $lists = $this->objectManager->get('Magento\Framework\Validator\Locale');
                    $errorMsg = $this->validateCodes($lists, $value, StoreConfigurationDataMapper::KEY_LANGUAGE);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_TIMEZONE:
                    /** @var Timezone $lists */
                    $lists = $this->objectManager->get('Magento\Framework\Validator\Timezone');
                    $errorMsg = $this->validateCodes($lists, $value, StoreConfigurationDataMapper::KEY_TIMEZONE);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_CURRENCY:
                    /** @var Currency $lists */
                    $lists = $this->objectManager->get('Magento\Framework\Validator\Currency');
                    $errorMsg = $this->validateCodes($lists, $value, StoreConfigurationDataMapper::KEY_CURRENCY);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_USE_SEF_URL:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_USE_SEF_URL);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_IS_SECURE:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_IS_SECURE);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case StoreConfigurationDataMapper::KEY_BASE_URL_SECURE:
                    try {
                        /** @var Validator $url */
                        $url = $this->objectManager->get('Magento\Framework\Url\Validator');
                        $errorMsgs = '';
                        if (!$url->isValid($value)) {
                            $errorMsgs = $url->getMessages();
                            if (!empty($errorMsgs)) {
                                $errors[] = '<error>' . 'Command option \''
                                    . StoreConfigurationDataMapper::KEY_BASE_URL_SECURE
                                    . '\': ' . $errorMsgs[Validator::INVALID_URL] .'</error>';
                            }
                        }
                        if (empty($errorMsgs) && strpos($value, 'https:') === false) {
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
                case StoreConfigurationDataMapper::KEY_JS_LOGGING:
                    $errorMsg = $this->validateBinaryValue(
                        $value,
                        StoreConfigurationDataMapper::KEY_JS_LOGGING
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

    /**
     * Validate codes for languages, currencies or timezones
     *
     * @param Locale|Timezone|Currency  $lists
     * @param string  $code
     * @param string  $type
     * @return string
     */
    private function validateCodes($lists, $code, $type)
    {
        $errorMsg = '';
        if (!$lists->isValid($code)) {
            $errorMsg = '<error>' . 'Command option \'' . $type . '\': Invalid value. To see possible values, '
                . "run command 'bin/magento info:" . $type . ':list\'.</error>';
        }
        return $errorMsg;
    }
}
