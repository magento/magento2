<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Magento\Framework\Validator\Locale as LocaleValidator;
use Magento\Framework\Validator\Timezone as TimezoneValidator;
use Magento\Framework\Validator\Currency as CurrencyValidator;
use Magento\Framework\Validator\Url as UrlValidator;

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
     * @deprecated
     */
    private $objectManager;

    /**
     * @var LocaleValidator
     */
    private $localeValidator;

    /**
     * @var TimezoneValidator
     */
    private $timezoneValidator;

    /**
     * @var CurrencyValidator
     */
    private $currencyValidator;

    /**
     * @var UrlValidator
     */
    private $urlValidator;

    /**
     * Inject dependencies
     *
     * @param InstallerFactory $installerFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ObjectManagerProvider $objectManagerProvider
     * @param LocaleValidator $localeValidator,
     * @param TimezoneValidator $timezoneValidator,
     * @param CurrencyValidator $currencyValidator,
     * @param UrlValidator $urlValidator
     */
    public function __construct(
        InstallerFactory $installerFactory,
        DeploymentConfig $deploymentConfig,
        ObjectManagerProvider $objectManagerProvider,
        LocaleValidator $localeValidator,
        TimezoneValidator $timezoneValidator,
        CurrencyValidator $currencyValidator,
        UrlValidator $urlValidator
    ) {
        $this->installerFactory = $installerFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->objectManager = $objectManagerProvider->get();
        $this->localeValidator = $localeValidator;
        $this->timezoneValidator = $timezoneValidator;
        $this->currencyValidator = $currencyValidator;
        $this->urlValidator = $urlValidator;
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
        $errorMsg = '';
        $options = $input->getOptions();
        foreach ($options as $key => $value) {
            if (!$value) {
                continue;
            }
            switch ($key) {
                case StoreConfigurationDataMapper::KEY_BASE_URL:
                    if (strcmp($value, '{{base_url}}') == 0) {
                        break;
                    }
                    $errorMsg = $this->validateUrl(
                        $value,
                        StoreConfigurationDataMapper::KEY_BASE_URL,
                        ['http', 'https']
                    );

                    break;
                case StoreConfigurationDataMapper::KEY_LANGUAGE:
                    $errorMsg = $this->validateCodes(
                        $this->localeValidator,
                        $value,
                        StoreConfigurationDataMapper::KEY_LANGUAGE
                    );
                    break;
                case StoreConfigurationDataMapper::KEY_TIMEZONE:
                    $errorMsg = $this->validateCodes(
                        $this->timezoneValidator,
                        $value,
                        StoreConfigurationDataMapper::KEY_TIMEZONE
                    );
                    break;
                case StoreConfigurationDataMapper::KEY_CURRENCY:
                    $errorMsg = $this->validateCodes(
                        $this->currencyValidator,
                        $value,
                        StoreConfigurationDataMapper::KEY_CURRENCY
                    );
                    break;
                case StoreConfigurationDataMapper::KEY_USE_SEF_URL:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_USE_SEF_URL);
                    break;
                case StoreConfigurationDataMapper::KEY_IS_SECURE:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_IS_SECURE);
                    break;
                case StoreConfigurationDataMapper::KEY_BASE_URL_SECURE:
                    $errorMsg = $this->validateUrl(
                        $value,
                        StoreConfigurationDataMapper::KEY_BASE_URL_SECURE,
                        ['https']
                    );
                    break;
                case StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN:
                    $errorMsg = $this->validateBinaryValue($value, StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN);
                    break;
                case StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY:
                    $errorMsg = $this->validateBinaryValue(
                        $value,
                        StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY
                    );
                    break;
                case StoreConfigurationDataMapper::KEY_JS_LOGGING:
                    $errorMsg = $this->validateBinaryValue(
                        $value,
                        StoreConfigurationDataMapper::KEY_JS_LOGGING
                    );
                    break;
            }
            if ($errorMsg !== '') {
                $errors[] = $errorMsg;
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
     * @param LocaleValidator|TimezoneValidator|CurrencyValidator  $lists
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

    /**
     * Validate URL
     *
     * @param string $url
     * @param string $option
     * @param array $allowedSchemes
     * @return string
     */
    private function validateUrl($url, $option, array $allowedSchemes)
    {
        $errorMsg = '';

        if (!$this->urlValidator->isValid($url, $allowedSchemes)) {
            $errorTemplate = '<error>Command option \'%s\': Invalid URL \'%s\'.'
                . ' Domain Name should contain only letters, digits and hyphen.'
                . ' And you should use only following schemes: \'%s\'.</error>';
            $errorMsg = sprintf(
                $errorTemplate,
                $option,
                $url,
                implode(', ', $allowedSchemes)
            );
        }

        return $errorMsg;
    }
}
