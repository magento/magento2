<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreStart

namespace Magento\Developer\Console\Command {
    use Symfony\Component\Console\Tester\CommandTester;

    $devTestsRunCommandTestPassthruReturnVar = null;

    /**
     * Mock for PHP builtin passthtru function
     *
     * @param string $command
     * @param int|null $return_var
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    function passthru($command, &$return_var = null)
    {
        global $devTestsRunCommandTestPassthruReturnVar;
        $return_var = $devTestsRunCommandTestPassthruReturnVar;
    }

    /**
     * Class DevTestsRunCommandTest
     *
     * Tests dev:tests:run command.  Only tests error case because DevTestsRunCommand calls phpunit with
     * passthru, so there is no good way to mock out running the tests.
     */
    class DevTestsRunCommandTest extends \PHPUnit\Framework\TestCase
    {

        /**
         * @var DevTestsRunCommand
         */
        private $command;

        protected function setUp(): void
        {
            $this->command = new DevTestsRunCommand();
        }

        public function testExecuteBadType()
        {
            $commandTester = new CommandTester($this->command);
            $commandTester->execute([DevTestsRunCommand::INPUT_ARG_TYPE => 'bad']);
            $this->assertStringContainsString('Invalid type: "bad"', $commandTester->getDisplay());
        }

        public function testPassArgumentsToPHPUnit()
        {
            global $devTestsRunCommandTestPassthruReturnVar;

            $devTestsRunCommandTestPassthruReturnVar = 0;

            $commandTester = new CommandTester($this->command);
            $commandTester->execute(
                [
                    DevTestsRunCommand::INPUT_ARG_TYPE => 'unit',
                    '-' . DevTestsRunCommand::INPUT_OPT_COMMAND_ARGUMENTS_SHORT => '--list-suites',
                ]
            );
            $this->assertStringContainsString(
                'phpunit  --list-suites',
                $commandTester->getDisplay(),
                'Parameters should be passed to PHPUnit'
            );
            $this->assertStringContainsString(
                'PASSED (',
                $commandTester->getDisplay(),
                'PHPUnit runs should have passed'
            );
        }

        public function testPassArgumentsToPHPUnitNegative()
        {
            global $devTestsRunCommandTestPassthruReturnVar;

            $devTestsRunCommandTestPassthruReturnVar = 255;

            $commandTester = new CommandTester($this->command);
            $commandTester->execute(
                [
                    DevTestsRunCommand::INPUT_ARG_TYPE => 'unit',
                    '-' . DevTestsRunCommand::INPUT_OPT_COMMAND_ARGUMENTS_SHORT => '--list-suites',
                ]
            );
            $this->assertStringContainsString(
                'phpunit  --list-suites',
                $commandTester->getDisplay(),
                'Parameters should be passed to PHPUnit'
            );
            $this->assertStringContainsString(
                'FAILED - ',
                $commandTester->getDisplay(),
                'PHPUnit runs should have passed'
            );
        }
    }
}
