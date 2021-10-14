<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\CodingStandard\Tool;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\CodingStandard\ToolInterface;

/**
 * PHP Code Sniffer tool wrapper
 */
class CodeSniffer implements ToolInterface, ExtensionInterface
{
    /**
     * Ruleset directory
     *
     * @var string
     */
    private $rulesetDir;

    /**
     * Report file
     *
     * @var string
     */
    private $reportFile;

    /**
     * PHPCS cli tool wrapper
     *
     * @var Wrapper
     */
    private $wrapper;

    /**
     * List of extensions for tool run
     *
     * @var array
     */
    private $extensions = [
        'php' => 'PHP',
        'phtml' => 'PHP',
    ];

    /**
     * Constructor
     *
     * @param string $rulesetDir \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     * @param Wrapper $wrapper
     */
    public function __construct($rulesetDir, $reportFile, Wrapper $wrapper)
    {
        $this->rulesetDir = $rulesetDir;
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!file_exists($rulesetDir) && file_exists($fullPath = realpath(__DIR__ . '/../../../../' . $rulesetDir))) {
            $this->rulesetDir = $fullPath;
        }
        $this->reportFile = $reportFile;
        $this->wrapper = $wrapper;
    }

    /**
     * @inheritdoc
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Whether the tool can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        return class_exists('\PHP_CodeSniffer\Runner');
    }

    /**
     * @inheritdoc
     */
    public function run(array $whiteList)
    {
        if (empty($whiteList)) {
            return 0;
        }

        if (!defined('PHP_CODESNIFFER_IN_TESTS')) {
            define('PHP_CODESNIFFER_IN_TESTS', true);
        }

        $this->wrapper->checkRequirements();
        $settings = [];
        $settings['files'] = $whiteList;
        $settings['standards'] = [$this->rulesetDir];
        $settings['extensions'] = $this->extensions;
        $settings['reports']['full'] = $this->reportFile;
        $settings['reportWidth'] = 120;
        $this->wrapper->setSettings($settings);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        ob_start();
        $result = $this->wrapper->runPHPCS();
        ob_end_clean();

        return $result;
    }
}
