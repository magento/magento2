<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Currency as CurrencyValidator;
use Magento\Framework\Validator\Locale as LocaleValidator;
use Magento\Framework\Validator\Timezone as TimezoneValidator;
use Magento\Framework\Validator\Url as UrlValidator;
use Magento\Setup\Console\Command\InstallStoreConfigurationCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\StoreConfigurationDataMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallStoreConfigurationCommandTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var InstallerFactory|MockObject
     */
    private $installerFactory;

    /**
     * @var Installer|MockObject
     */
    private $installer;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var LocaleValidator|MockObject
     */
    private $localeValidatorMock;

    /**
     * @var TimezoneValidator|MockObject
     */
    private $timezoneValidatorMock;

    /**
     * @var CurrencyValidator|MockObject
     */
    private $currencyValidatorMock;

    /**
     * @var UrlValidator|MockObject
     */
    private $urlValidatorMock;

    /**
     * @var InstallStoreConfigurationCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->urlValidatorMock = $this->createMock(UrlValidator::class);
        $this->localeValidatorMock = $this->createMock(LocaleValidator::class);
        $this->timezoneValidatorMock = $this->createMock(TimezoneValidator::class);
        $this->currencyValidatorMock = $this->createMock(CurrencyValidator::class);

        $this->installerFactory = $this->createMock(InstallerFactory::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->installer = $this->createMock(Installer::class);
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->command = new InstallStoreConfigurationCommand(
            $this->installerFactory,
            $this->deploymentConfig,
            $objectManagerProvider,
            $this->localeValidatorMock,
            $this->timezoneValidatorMock,
            $this->currencyValidatorMock,
            $this->urlValidatorMock
        );
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->installer->expects($this->once())
            ->method('installUserConfig');
        $this->installerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->installer);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->installerFactory->expects($this->never())
            ->method('create');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat(
            "Store settings can't be saved because the Magento application is not installed.%w",
            $tester->getDisplay()
        );
    }

    /**
     * @dataProvider validateDataProvider
     * @param array $option
     * @param string $error
     */
    public function testExecuteInvalidData(array $option, $error)
    {
        $this->localeValidatorMock->expects($this->any())->method('isValid')->willReturn(false);
        $this->timezoneValidatorMock->expects($this->any())->method('isValid')->willReturn(false);
        $this->currencyValidatorMock->expects($this->any())->method('isValid')->willReturn(false);
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(false);

        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->installerFactory->expects($this->never())
            ->method('create');
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($option);
        $this->assertStringContainsString($error, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                ['--' . StoreConfigurationDataMapper::KEY_BASE_URL => 'sampleUrl'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL . '\': Invalid URL \'sampleUrl\'.'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_BASE_URL => 'http://example.com_test'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL
                    . '\': Invalid URL \'http://example.com_test\'.'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_LANGUAGE => 'sampleLanguage'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_LANGUAGE
                    . '\': Invalid value. To see possible values, run command \'bin/magento info:language:list\'.'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_TIMEZONE => 'sampleTimezone'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_TIMEZONE
                    . '\': Invalid value. To see possible values, run command \'bin/magento info:timezone:list\'.'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_CURRENCY => 'sampleLanguage'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_CURRENCY
                    . '\': Invalid value. To see possible values, run command \'bin/magento info:currency:list\'.'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_USE_SEF_URL => 'invalidValue'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_USE_SEF_URL
                    . '\': Invalid value. Possible values (0|1).'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_IS_SECURE => 'invalidValue'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_IS_SECURE
                    . '\': Invalid value. Possible values (0|1).'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_BASE_URL_SECURE => 'http://www.sample.com'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL_SECURE
                    . '\': Invalid URL \'http://www.sample.com\'.'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN => 'invalidValue'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_IS_SECURE_ADMIN
                    . '\': Invalid value. Possible values (0|1).'
            ],
            [
                ['--' . StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY => 'invalidValue'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_ADMIN_USE_SECURITY_KEY
                    . '\': Invalid value. Possible values (0|1).'
            ],

        ];
    }
}
