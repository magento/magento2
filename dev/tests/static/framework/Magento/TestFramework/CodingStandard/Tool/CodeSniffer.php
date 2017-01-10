<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Code Sniffer tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\CodingStandard\ToolInterface;

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
    private $extensions = ['php'];

    /**
     * Constructor
     *
     * @param string $rulesetDir \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     * @param Wrapper $wrapper
     */
    public function __construct($rulesetDir, $reportFile, Wrapper $wrapper)
    {
        $this->reportFile = $reportFile;
        $this->rulesetDir = $rulesetDir;
        $this->wrapper = $wrapper;
    }

    /**
     * {@inheritdoc}
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
        return class_exists('PHP_CodeSniffer_CLI');
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $whiteList)
    {
        if (empty($whiteList)) {
            return 0;
        }

        $this->wrapper->checkRequirements();
        $settings = $this->wrapper->getDefaults();
        $settings['files'] = $whiteList;
        $settings['standard'] = [$this->rulesetDir];
        $settings['extensions'] = $this->extensions;
        $settings['warningSeverity'] = 0;
        $settings['reports']['full'] = $this->reportFile;

        $this->wrapper->setValues($settings);

        ob_start();
        $result = $this->wrapper->process();
        ob_end_clean();

        return $result;
    }
}
