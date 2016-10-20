<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InstallStoreConfigurationCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Setup\Model\Installer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\StoreConfigurationDataMapper;
use Magento\Framework\Validator\Url as UrlValidator;
use Magento\Framework\Validator\Locale as LocaleValidator;
use Magento\Framework\Validator\Timezone as TimezoneValidator;
use Magento\Framework\Validator\Currency as CurrencyValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallStoreConfigurationCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Setup\Model\InstallerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installerFactory;

    /**
     * @var Installer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installer;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var LocaleValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeValidatorMock;

    /**
     * @var TimezoneValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timezoneValidatorMock;

    /**
     * @var CurrencyValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyValidatorMock;

    /**
     * @var UrlValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlValidatorMock;

    /**
     * @var InstallStoreConfigurationCommand
     */
    private $command;

    protected function setUp()
    {
        $this->urlValidatorMock = $this->getMock(UrlValidator::class, [], [], '', false);
        $this->localeValidatorMock = $this->getMock(LocaleValidator::class, [], [], '', false);
        $this->timezoneValidatorMock = $this->getMock(TimezoneValidator::class, [], [], '', false);
        $this->currencyValidatorMock = $this->getMock(CurrencyValidator::class, [], [], '', false);

        $this->installerFactory = $this->getMock(\Magento\Setup\Model\InstallerFactory::class, [], [], '', false);
        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->installer = $this->getMock(\Magento\Setup\Model\Installer::class, [], [], '', false);
        $objectManagerProvider = $this->getMock(
            \Magento\Setup\Model\ObjectManagerProvider::class,
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
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
            ->will($this->returnValue(true));
        $this->installer->expects($this->once())
            ->method('installUserConfig');
        $this->installerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->installer));
        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(false));
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
            ->will($this->returnValue(true));
        $this->installerFactory->expects($this->never())
            ->method('create');
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($option);
        $this->assertContains($error, $commandTester->getDisplay());
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
