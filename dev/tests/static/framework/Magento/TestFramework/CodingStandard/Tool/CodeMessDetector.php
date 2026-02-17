<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * PHP Code Mess v1.3.3 tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

use \Magento\TestFramework\CodingStandard\ToolInterface;

class CodeMessDetector implements ToolInterface
{
    /**
     * Ruleset directory
     *
     * @var string
     */
    private $rulesetFile;

    /**
     * @var string
     */
    private $reportFile;

    /**
     * @param string $rulesetFile \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($rulesetFile, $reportFile)
    {
        $this->reportFile = $reportFile;
        $this->rulesetFile = $rulesetFile;
    }

    /**
     * Whether the tool can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        return class_exists(\PHPMD\TextUI\Command::class);
    }

    /**
     * @inheritdoc
     */
    public function run(array $whiteList)
    {
        if (empty($whiteList)) {
            return class_exists(\PHPMD\TextUI\ExitCode::class) ? \PHPMD\TextUI\ExitCode::Success : 0;
        }

        $command = new \PHPMD\TextUI\Command();
        // Build ArrayInput matching PHPMD's Symfony Command definition:
        $input = new \Symfony\Component\Console\Input\ArrayInput(
            [
                'paths' => array_values($whiteList),
                '--format' => 'text',
                '--ruleset' => [realpath($this->rulesetFile)],
                '--reportfile-text' => $this->reportFile,
            ],
            $command->getDefinition()
        );
        $output = new \Symfony\Component\Console\Output\NullOutput();
        return $command->run($input, $output);
    }
}
