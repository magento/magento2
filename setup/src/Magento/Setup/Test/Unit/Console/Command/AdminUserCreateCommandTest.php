<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\AdminUserCreateCommand;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\User\Model\UserValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminUserCreateCommandTest extends TestCase
{
    /**
     * @var MockObject|QuestionHelper
     */
    private $questionHelperMock;

    /**
     * @var MockObject|InstallerFactory
     */
    private $installerFactoryMock;

    /**
     * @var MockObject|AdminUserCreateCommand
     */
    private $command;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->installerFactoryMock = $this->createMock(InstallerFactory::class);
        $this->command = new AdminUserCreateCommand($this->installerFactoryMock, new UserValidationRules());

        $this->questionHelperMock = $this->getMockBuilder(QuestionHelper::class)
            ->onlyMethods(['ask'])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $options = [
            '--' . AdminAccount::KEY_USER => 'user',
            '--' . AdminAccount::KEY_PASSWORD => '123123q',
            '--' . AdminAccount::KEY_EMAIL => 'test@test.com',
            '--' . AdminAccount::KEY_FIRST_NAME => 'John',
            '--' . AdminAccount::KEY_LAST_NAME => 'Doe'
        ];
        $data = [
            AdminAccount::KEY_USER => 'user',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_EMAIL => 'test@test.com',
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            InitParamListener::BOOTSTRAP_PARAM => null
        ];
        $commandTester = new CommandTester($this->command);
        $installerMock = $this->createMock(Installer::class);
        $installerMock->expects($this->once())->method('installAdminUser')->with($data);
        $this->installerFactoryMock->expects($this->once())->method('create')->willReturn($installerMock);
        $commandTester->execute($options, ['interactive' => false]);
        $this->assertEquals('Created Magento administrator user named user' . PHP_EOL, $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testInteraction(): void
    {
        $application = new Application();
        $application->add($this->command);

        $this->questionHelperMock
            ->method('ask')
            ->willReturnOnConsecutiveCalls('admin', 'Password123', 'john.doe@example.com', 'John', 'Doe');

        // We override the standard helper with our mock
        $this->command->getHelperSet()->set($this->questionHelperMock, 'question');

        $installerMock = $this->createMock(Installer::class);

        $expectedData = [
            'admin-user' => 'admin',
            'admin-password' => 'Password123',
            'admin-email' => 'john.doe@example.com',
            'admin-firstname' => 'John',
            'admin-lastname' => 'Doe',
            'magento-init-params' => null,
            'help' => false,
            'quiet' => false,
            'verbose' => false,
            'version' => false,
            'ansi' => null,
            'no-interaction' => false
        ];

        $installerMock->expects($this->once())->method('installAdminUser')->with($expectedData);
        $this->installerFactoryMock->expects($this->once())->method('create')->willReturn($installerMock);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $this->assertEquals(
            'Created Magento administrator user named admin' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @param int $mode
     * @param string $description
     *
     * @return void
     * @dataProvider getOptionListDataProvider
     */
    public function testGetOptionsList(int $mode, string $description): void
    {
        /* @var $argsList InputArgument[] */
        $argsList = $this->command->getOptionsList($mode);
        $this->assertEquals(AdminAccount::KEY_EMAIL, $argsList[2]->getName());
        $this->assertEquals($description, $argsList[2]->getDescription());
    }

    /**
     * @return array
     */
    public static function getOptionListDataProvider(): array
    {
        return [
            [
                'mode' => InputOption::VALUE_REQUIRED,
                'description' => '(Required) Admin email'
            ],
            [
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'Admin email'
            ]
        ];
    }

    /**
     * @param bool[] $options
     * @param string[] $errors
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $options, array $errors): void
    {
        $inputMock = $this->getMockForAbstractClass(
            InputInterface::class,
            [],
            '',
            false
        );
        $inputMock
            ->method('getOption')
            ->willReturnOnConsecutiveCalls(...$options);

        $this->assertEquals($errors, $this->command->validate($inputMock));
    }

    /**
     * @return array
     */
    public static function validateDataProvider(): array
    {
        return [
            [
                [null, 'Doe', 'admin', 'test@test.com', '123123q', '123123q'],
                ['"First Name" is required. Enter and try again.']
            ],
            [
                ['John', null, null, 'test@test.com', '123123q', '123123q'],
                ['"User Name" is required. Enter and try again.', '"Last Name" is required. Enter and try again.'],
            ],
            [['John', 'Doe', 'admin', null, '123123q', '123123q'], ['Please enter a valid email.']],
            [
                ['John', 'Doe', 'admin', 'test', '123123q', '123123q'],
                ["'test' is not a valid email address in the basic format local-part@hostname"]
            ],
            [
                ['John', 'Doe', 'admin', 'test@test.com', '', ''],
                [
                    'Password is required field.',
                    'Your password must be at least 7 characters.',
                    'Your password must include both numeric and alphabetic characters.'
                ]
            ],
            [
                ['John', 'Doe', 'admin', 'test@test.com', '123123', '123123'],
                [
                    'Your password must be at least 7 characters.',
                    'Your password must include both numeric and alphabetic characters.'
                ]
            ],
            [
                ['John', 'Doe', 'admin', 'test@test.com', '1231231', '1231231'],
                ['Your password must include both numeric and alphabetic characters.']
            ],
            [['John', 'Doe', 'admin', 'test@test.com', '123123q', '123123q'], []],
        ];
    }
}
