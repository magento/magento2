<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\AdminUserCreateCommand;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\User\Model\UserValidationRules;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminUserCreateCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Console\Helper\QuestionHelper
     */
    private $questionHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\InstallerFactory
     */
    private $installerFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AdminUserCreateCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->installerFactoryMock = $this->createMock(\Magento\Setup\Model\InstallerFactory::class);
        $this->command = new AdminUserCreateCommand($this->installerFactoryMock, new UserValidationRules());

        $this->questionHelperMock = $this->getMockBuilder(QuestionHelper::class)
            ->setMethods(['ask'])
            ->getMock();
    }

    public function testExecute()
    {
        $options = [
            '--' . AdminAccount::KEY_USER => 'user',
            '--' . AdminAccount::KEY_PASSWORD => '123123q',
            '--' . AdminAccount::KEY_EMAIL => 'test@test.com',
            '--' . AdminAccount::KEY_FIRST_NAME => 'John',
            '--' . AdminAccount::KEY_LAST_NAME => 'Doe',
        ];
        $data = [
            AdminAccount::KEY_USER => 'user',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_EMAIL => 'test@test.com',
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            InitParamListener::BOOTSTRAP_PARAM => null,
        ];
        $commandTester = new CommandTester($this->command);
        $installerMock = $this->createMock(\Magento\Setup\Model\Installer::class);
        $installerMock->expects($this->once())->method('installAdminUser')->with($data);
        $this->installerFactoryMock->expects($this->once())->method('create')->willReturn($installerMock);
        $commandTester->execute($options, ['interactive' => false]);
        $this->assertEquals('Created Magento administrator user named user' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testInteraction()
    {
        $application = new Application();
        $application->add($this->command);

        $this->questionHelperMock->expects($this->at(0))
            ->method('ask')
            ->willReturn('admin');

        $this->questionHelperMock->expects($this->at(1))
            ->method('ask')
            ->willReturn('Password123');

        $this->questionHelperMock->expects($this->at(2))
            ->method('ask')
            ->willReturn('john.doe@example.com');

        $this->questionHelperMock->expects($this->at(3))
            ->method('ask')
            ->willReturn('John');

        $this->questionHelperMock->expects($this->at(4))
            ->method('ask')
            ->willReturn('Doe');

        // We override the standard helper with our mock
        $this->command->getHelperSet()->set($this->questionHelperMock, 'question');

        $installerMock = $this->createMock(\Magento\Setup\Model\Installer::class);

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
            'ansi' => false,
            'no-ansi' => false,
            'no-interaction' => false,
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
     * @dataProvider getOptionListDataProvider
     */
    public function testGetOptionsList($mode, $description)
    {
        /* @var $argsList \Symfony\Component\Console\Input\InputArgument[] */
        $argsList = $this->command->getOptionsList($mode);
        $this->assertEquals(AdminAccount::KEY_EMAIL, $argsList[2]->getName());
        $this->assertEquals($description, $argsList[2]->getDescription());
    }

    /**
     * @return array
     */
    public function getOptionListDataProvider()
    {
        return [
            [
                'mode' => InputOption::VALUE_REQUIRED,
                'description' => '(Required) Admin email',
            ],
            [
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'Admin email',
            ],
        ];
    }

    /**
     * @dataProvider validateDataProvider
     * @param bool[] $options
     * @param string[] $errors
     */
    public function testValidate(array $options, array $errors)
    {
        $inputMock = $this->getMockForAbstractClass(
            \Symfony\Component\Console\Input\InputInterface::class,
            [],
            '',
            false
        );
        $index = 0;
        foreach ($options as $option) {
            $inputMock->expects($this->at($index++))->method('getOption')->willReturn($option);
        }
        $this->assertEquals($errors, $this->command->validate($inputMock));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
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
