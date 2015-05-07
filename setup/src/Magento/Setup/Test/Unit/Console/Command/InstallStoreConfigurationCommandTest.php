<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InstallStoreConfigurationCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Setup\Model\Installer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\StoreConfigurationDataMapper;

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
     * @var InstallStoreConfigurationCommand
     */
    private $command;

    protected function setUp()
    {
        $this->installerFactory = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->installer = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager );
        $this->command = new InstallStoreConfigurationCommand(
            $this->installerFactory,
            $this->deploymentConfig,
            $objectManagerProvider
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
        $localizedException= $this->getMock('Magento\Framework\Exception\LocalizedException', [], [], '', false);
        $localizedException->expects($this->any())->method('getLogMessage')
            ->will($this->returnValue('Invalid value. Value must be a URL or one of placeholders:'));
        $baseUrl= $this->getMock('Magento\Config\Model\Config\Backend\Baseurl', [], [], '', false);
        $baseUrl->expects($this->any())->method('beforeSave')->will($this->throwException($localizedException));

        $lists= $this->getMock('Magento\Setup\Model\Lists', [], [], '', false);
        $lists->expects($this->any())->method('getLocaleList')->will($this->returnValue(['A' => 'B']));
        $lists->expects($this->any())->method('getTimezoneList')->will($this->returnValue(['C' => 'D']));
        $lists->expects($this->any())->method('getCurrencyList')->will($this->returnValue(['E' => 'F']));

        $returnValueMapOM = [
            [
                'Magento\Config\Model\Config\Backend\Baseurl',
                $baseUrl
            ],
            [
                'Magento\Setup\Model\Lists',
                $lists
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($returnValueMapOM));
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $this->installerFactory->expects($this->never())
            ->method('create');
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($option);
        $this->assertEquals($error . PHP_EOL, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                ['--' . StoreConfigurationDataMapper::KEY_BASE_URL => 'sampleUrl'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL
                . '\': Invalid value. Value must be a URL or one of placeholders:'
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
                ['--' . StoreConfigurationDataMapper::KEY_BASE_URL_SECURE => 'sampleUrl'],
                'Command option \'' . StoreConfigurationDataMapper::KEY_BASE_URL_SECURE
                . '\': Invalid value. Value must be a URL or one of placeholders:'
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
