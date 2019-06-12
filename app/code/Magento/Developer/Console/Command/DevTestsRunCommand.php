<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DevTestsRunCommand
 *
 * Runs tests (unit, static, integration, etc.)
 */
class DevTestsRunCommand extends Command
{
    /**
     * input parameter parameter
     */
    const INPUT_ARG_TYPE = 'type';

    /**
     * PHPUnit arguments parameter
     */
    const INPUT_OPT_COMMAND_ARGUMENTS       = 'arguments';
    const INPUT_OPT_COMMAND_ARGUMENTS_SHORT = 'c';

    /**
     * command name
     */
    const COMMAND_NAME = 'dev:tests:run';

    /**
     * Maps types (from user input) to phpunit test names
     *
     * @var array
     */
    private $types;

    /**
     * Maps phpunit test names to directory and target name
     *
     * @var array
     */
    private $commands;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setupTestInfo();
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Runs tests');

        $this->addArgument(
            self::INPUT_ARG_TYPE,
            InputArgument::OPTIONAL,
            'Type of test to run. Available types: ' . implode(', ', array_keys($this->types)),
            'default'
        );
        $this->addOption(
            self::INPUT_OPT_COMMAND_ARGUMENTS,
            self::INPUT_OPT_COMMAND_ARGUMENTS_SHORT,
            InputOption::VALUE_REQUIRED,
            'Additional arguments for PHPUnit. Example: "-c\'--filter=MyTest\'" (no spaces)',
            ''
        );
        parent::configure();
    }

    /**
     * Run the tests
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int Non zero if invalid type, 0 otherwise
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* Validate type argument is valid */
        $type = $input->getArgument(self::INPUT_ARG_TYPE);
        if (!isset($this->types[$type])) {
            $output->writeln(
                'Invalid type: "' . $type . '". Available types: ' . implode(', ', array_keys($this->types))
            );
            return 1;
        }

        $vendorDir = require BP . '/app/etc/vendor_path.php';

        $failures = [];
        $runCommands = $this->types[$type];
        foreach ($runCommands as $key) {
            list($dir, $options) = $this->commands[$key];
            $dirName = realpath(BP . '/dev/tests/' . $dir);
            chdir($dirName);
            $command = PHP_BINARY . ' ' . BP . '/' . $vendorDir . '/phpunit/phpunit/phpunit ' . $options;
            if ($commandArguments = $input->getOption(self::INPUT_OPT_COMMAND_ARGUMENTS)) {
                $command .= ' ' . $commandArguments;
            }
            $message = $dirName . '> ' . $command;
            $output->writeln(['', str_pad("---- {$message} ", 70, '-'), '']);
            passthru($command, $returnVal);
            if ($returnVal) {
                $failures[] = $message;
            }
        }

        $output->writeln(str_repeat('-', 70));
        if ($failures) {
            $output->writeln("FAILED - " . count($failures) . ' of ' . count($runCommands) . ":");
            foreach ($failures as $message) {
                $output->writeln(' - ' . $message);
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } else {
            $output->writeln('PASSED (' . count($runCommands) . ')');
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Fills in arrays that link test types to php unit tests and directories.
     *
     * @return void
     */
    private function setupTestInfo()
    {
        $this->commands = [
            'unit'                   => ['../tests/unit', ''],
            'unit-static'            => ['../tests/static/framework/tests/unit', ''],
            'unit-integration'       => ['../tests/integration/framework/tests/unit', ''],
            'integration'            => ['../tests/integration', ''],
            'integration-integrity'  => ['../tests/integration', ' testsuite/Magento/Test/Integrity'],
            'static-default'         => ['../tests/static', ''],
            'static-legacy'          => ['../tests/static', ' testsuite/Magento/Test/Legacy'],
            'static-integration-js'  => ['../tests/static', ' testsuite/Magento/Test/Js/Exemplar'],
        ];
        $this->types = [
            'all'             => array_keys($this->commands),
            'unit'            => ['unit', 'unit-static', 'unit-integration'],
            'integration'     => ['integration'],
            'integration-all' => ['integration', 'integration-integrity'],
            'static'          => ['static-default'],
            'static-all'      => ['static-default', 'static-legacy', 'static-integration-js'],
            'integrity'       => ['static-default', 'static-legacy', 'integration-integrity'],
            'legacy'          => ['static-legacy'],
            'default'         => [
                'unit',
                'unit-static',
                'unit-integration',
                'integration',
                'static-default',
            ],
        ];
    }
}
