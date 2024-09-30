<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Console\Command;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Config\Model\Config\PathValidator;
use Magento\Config\Model\Config\Structure\Converter;
use Magento\Config\Model\Config\Structure\Data as StructureData;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Tests the different flows of config:set command.
 *
 * @inheritdoc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation enabled
 */
class ConfigSetCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

    /**
     * @var OutputInterface|Mock
     */
    private $outputMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $config;

    /**
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        Bootstrap::getInstance()->reinitialize();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->extendSystemStructure();

        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->arrayManager = $this->objectManager->get(ArrayManager::class);
        $this->appConfig = $this->objectManager->get(ReinitableConfigInterface::class);

        // Snapshot of configuration.
        $this->config = $this->loadConfig();

        // Mocks for objects.
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $this->config]);
        $this->appConfig->reinit();
    }

    /**
     * Add test system structure to main system structure
     *
     * @return void
     */
    private function extendSystemStructure()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/../../_files/system.xml');
        $converter = $this->objectManager->get(Converter::class);
        $systemConfig = $converter->convert($document);
        $structureData = $this->objectManager->get(StructureData::class);
        $structureData->merge($systemConfig);
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * Tests lockable flow.
     * Expects to save value and then error on saving duplicate value.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeCode
     * @magentoDbIsolation enabled
     * @dataProvider runLockDataProvider
     */
    public function testRunLockEnv($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_LOCK_ENV, true],
                [ConfigSetCommand::OPTION_SCOPE, $scope],
                [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode]
            ]);
        $this->outputMock->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                ['<info>Value was saved in app/etc/env.php and locked.</info>'],
                ['<info>Value was saved in app/etc/env.php and locked.</info>']
            );

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        /** @var ConfigPathResolver $resolver */
        $resolver = $this->objectManager->get(ConfigPathResolver::class);
        $status = $command->run($this->inputMock, $this->outputMock);
        $configPath = $resolver->resolve($path, $scope, $scopeCode, 'system');

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame($value, $this->arrayManager->get($configPath, $this->loadConfig()));

        $status = $command->run($this->inputMock, $this->outputMock);
        $this->appConfig->reinit();

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
    }

    /**
     * Retrieves variations with path, value, scope and scope code.
     *
     * @return array
     */
    public function runLockDataProvider()
    {
        return [
            ['general/region/display_all', '1'],
            ['general/region/state_required', 'BR,FR', ScopeInterface::SCOPE_WEBSITE, 'base'],
            ['admin/security/use_form_key', '0'],
            ['general/group/subgroup/field', 'default_value'],
            ['general/group/subgroup/field', 'website_value', ScopeInterface::SCOPE_WEBSITE, 'base'],
        ];
    }

    /**
     * Tests the extended flow.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeCode
     * @magentoDbIsolation enabled
     * @dataProvider runExtendedDataProvider
     */
    public function testRunExtended(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $arguments = [
            [ConfigSetCommand::ARG_PATH, $path],
            [ConfigSetCommand::ARG_VALUE, $value]
        ];
        $options = [
            [ConfigSetCommand::OPTION_SCOPE, $scope],
            [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode]
        ];
        $optionsLock = array_merge($options, [[ConfigSetCommand::OPTION_LOCK_ENV, true]]);

        /** @var ConfigPathResolver $resolver */
        $resolver = $this->objectManager->get(ConfigPathResolver::class);
        /** @var array $configPath */
        $configPath = $resolver->resolve($path, $scope, $scopeCode, 'system');

        $this->runCommand($arguments, $options, '<info>Value was saved.</info>');
        $this->runCommand($arguments, $options, '<info>Value was saved.</info>');

        $this->assertSame(
            $value,
            $this->scopeConfig->getValue($path, $scope, $scopeCode)
        );
        $this->assertNull($this->arrayManager->get($configPath, $this->loadConfig()));

        $this->runCommand($arguments, $optionsLock, '<info>Value was saved in app/etc/env.php and locked.</info>');
        $this->runCommand($arguments, $optionsLock, '<info>Value was saved in app/etc/env.php and locked.</info>');

        $this->assertSame($value, $this->arrayManager->get($configPath, $this->loadConfig()));
    }

    /**
     * Runs pre-configured command.
     *
     * @param array $arguments
     * @param array $options
     * @param string $expectedMessage
     * @param int $expectedCode
     */
    private function runCommand(
        array $arguments,
        array $options,
        $expectedMessage = '',
        $expectedCode = Cli::RETURN_SUCCESS
    ) {
        $input = clone $this->inputMock;
        $output = clone $this->outputMock;

        $input->expects($this->any())
            ->method('getArgument')
            ->willReturnMap($arguments);
        $input->expects($this->any())
            ->method('getOption')
            ->willReturnMap($options);
        $output->expects($this->once())
            ->method('writeln')
            ->with($expectedMessage);

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        $status = $command->run($input, $output);
        $this->appConfig->reinit();

        $this->assertSame($expectedCode, $status);
    }

    /**
     * Retrieves variations with path, value, scope and scope code.
     *
     * @return array
     */
    public function runExtendedDataProvider()
    {
        return $this->runLockDataProvider();
    }

    /**
     * @param string $path Config path
     * @param string $value Value of config is tried to be set
     * @param string $message Message command output
     * @param string $scope
     * @param $scopeCode string|null
     * @dataProvider configSetValidationErrorDataProvider
     * @magentoDbIsolation disabled
     */
    public function testConfigSetValidationError(
        $path,
        $value,
        $message,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->setConfigFailure($path, $value, $message, $scope, $scopeCode);
    }

    /**
     * Data provider for testConfigSetValidationError
     *
     * @return array
     */
    public function configSetValidationErrorDataProvider()
    {
        return [
            //wrong value for URL - checked by backend model of URL field
            [
                Custom::XML_PATH_UNSECURE_BASE_URL,
                'value',
                'Invalid Base URL. Value must be a URL or one of placeholders: {{base_url}}'
            ],
            //set not existed field path
            [
                'test/test/test',
                'value',
                'The "test/test/test" path doesn\'t exist. Verify and try again.'
            ],
            //wrong scope or scope code
            [
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                'en_UK',
                'A scope is missing. Enter a scope and try again.',
                ''
            ],
            [
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                'en_UK',
                'A scope code is missing. Enter a code and try again.',
                ScopeInterface::SCOPE_WEBSITE
            ],
            [
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                'en_UK',
                'A scope code is missing. Enter a code and try again.',
                ScopeInterface::SCOPE_STORE
            ],
            [
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                'en_UK',
                'The "wrong_scope" value doesn\'t exist. Enter another value and try again.',
                'wrong_scope',
                'base'
            ],
            [
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                'en_UK',
                'The "wrong_website_code" value doesn\'t exist. Enter another value and try again.',
                ScopeInterface::SCOPE_WEBSITE,
                'wrong_website_code'
            ],
            [
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                'en_UK',
                'The "wrong_store_code" value doesn\'t exist. Enter another value and try again.',
                ScopeInterface::SCOPE_STORE,
                'wrong_store_code'
            ],
            [
                Currency::XML_PATH_CURRENCY_DEFAULT,
                'GBP',
                'Sorry, the default display currency you selected is not available in allowed currencies.'
            ],
            [
                Currency::XML_PATH_CURRENCY_ALLOW,
                'GBP',
                'Default display currency "US Dollar" is not available in allowed currencies.'
            ]
        ];
    }

    /**
     * Saving values with successful validation
     *
     * @magentoDbIsolation enabled
     */
    public function testConfigSetCurrency()
    {
        /**
         * Checking saving currency as they are depend on each other.
         * Default currency can not be changed to new value if this value does not exist in allowed currency
         * that is why allowed currency is changed first by adding necessary value,
         * then old value is removed after changing default currency
         */
        $this->setConfigSuccess(Currency::XML_PATH_CURRENCY_ALLOW, 'USD,GBP');
        $this->setConfigSuccess(Currency::XML_PATH_CURRENCY_DEFAULT, 'GBP');
        $this->setConfigSuccess(Currency::XML_PATH_CURRENCY_ALLOW, 'GBP');
    }

    /**
     * Saving values with successful validation
     *
     * @dataProvider configSetValidDataProvider
     * @magentoDbIsolation enabled
     */
    public function testConfigSetValid()
    {
        $this->setConfigSuccess(Custom::XML_PATH_UNSECURE_BASE_URL, 'http://magento2.local/');
        $this->setConfigSuccess(Custom::XML_PATH_GENERAL_LOCALE_CODE, 'en_UK', ScopeInterface::SCOPE_WEBSITE, 'base');
        $this->setConfigSuccess(Custom::XML_PATH_GENERAL_LOCALE_CODE, 'en_AU', ScopeInterface::SCOPE_STORE, 'default');
    }

    /**
     * Data provider for testConfigSetValid
     *
     * @return array
     */
    public function configSetValidDataProvider()
    {
        return [
            [Custom::XML_PATH_UNSECURE_BASE_URL, 'http://magento2.local/'],
            [Custom::XML_PATH_GENERAL_LOCALE_CODE, 'en_UK', ScopeInterface::SCOPE_WEBSITE, 'base'],
            [Custom::XML_PATH_GENERAL_LOCALE_CODE, 'en_AU', ScopeInterface::SCOPE_STORE, 'default'],
            [Custom::XML_PATH_ADMIN_SECURITY_USEFORMKEY, '0']
        ];
    }

    /**
     * Test validate path when field has custom config_path
     */
    public function testValidatePathWithCustomConfigPath(): void
    {
        $pathValidator = $this->objectManager->get(PathValidator::class);
        $this->assertTrue($pathValidator->validate('general/group/subgroup/second_field'));
    }

    /**
     * Set configuration and check this value from DB with success message this command should display
     *
     * @param string $path Config path
     * @param string $value Value of config is tried to be set
     * @param string $scope
     * @param string|null $scopeCode
     */
    private function setConfigSuccess(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $status = $this->setConfig($path, $value, '<info>Value was saved.</info>', $scope, $scopeCode);
        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame(
            $value,
            $this->scopeConfig->getValue($path, $scope, $scopeCode)
        );
    }

    /**
     * Set configuration value with some error
     * and check that this value was not saved to DB and appropriate error message was displayed
     *
     * @param string $path Config path
     * @param string $value Value of config is tried to be set
     * @param string $message Message command output
     * @param string $scope
     * @param string|null $scopeCode
     */
    private function setConfigFailure(
        $path,
        $value,
        $message,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $status = $this->setConfig($path, $value, '<error>' . $message . '</error>', $scope, $scopeCode);
        $this->assertSame(Cli::RETURN_FAILURE, $status);
        $this->assertNotSame(
            $value,
            $this->scopeConfig->getValue($path),
            "Values are the same '$value' and '{$this->scopeConfig->getValue($path)}' for $path"
        );
    }

    /**
     * @param string $path Config path
     * @param string $value Value of config is tried to be set
     * @param string $message Message command output
     * @param string $scope
     * @param string|null $scopeCode
     * @return int Status that command returned
     */
    private function setConfig($path, $value, $message, $scope, $scopeCode)
    {
        $input = clone $this->inputMock;
        $output = clone $this->outputMock;
        $input->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $input->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, $scope],
                [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode]
            ]);
        $output->expects($this->once())
            ->method('writeln')
            ->with($message);

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        $status = $command->run($input, $output);
        return $status;
    }
}
