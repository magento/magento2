<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Helper\FormatterHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * PSR logger implementation for CLI
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * Type for informational message
     */
    const INFO = 'info';

    /**
     * Type for error message
     */
    const ERROR = 'error';

    /**
     * Public static files directory read interface
     *
     * @var ReadInterface
     */
    private $tmpDir;

    /**
     * Console output interface
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * Helper for preparing data of specific formats (date, percentage, etc)
     *
     * @var FormatterHelper
     */
    private $formatterHelper;

    /**
     * Maximum progress bar row string length
     *
     * @var int
     */
    private $initialMaxBarSize = 0;

    /**
     * Number of rendered lines
     *
     * Used for clearing previously rendered progress bars
     *
     * @var int
     */
    private $renderedLines = 0;

    /**
     * Time of previous rendering tick
     *
     * @var int
     */
    private $lastTimeRefreshed = 0;

    /**
     * @var array
     */
    private $verbosityLevelMap = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG
    ];

    /**
     * @var array
     */
    private $formatLevelMap = [
        LogLevel::EMERGENCY => self::ERROR,
        LogLevel::ALERT => self::ERROR,
        LogLevel::CRITICAL => self::ERROR,
        LogLevel::ERROR => self::ERROR,
        LogLevel::WARNING => self::INFO,
        LogLevel::NOTICE => self::INFO,
        LogLevel::INFO => self::INFO,
        LogLevel::DEBUG => self::INFO
    ];

    /**
     * Running deployment processes info
     *
     * @var array[]
     */
    private $processes = [];

    /**
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param FormatterHelper $formatterHelper
     * @param array $verbosityLevelMap
     * @param array $formatLevelMap
     */
    public function __construct(
        Filesystem $filesystem,
        OutputInterface $output,
        FormatterHelper $formatterHelper,
        array $verbosityLevelMap = [],
        array $formatLevelMap = []
    ) {
        $this->tmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP_MATERIALIZATION_DIR);
        $this->output = $output;
        $this->formatterHelper = $formatterHelper;
        $this->verbosityLevelMap = $verbosityLevelMap + $this->verbosityLevelMap;
        $this->formatLevelMap = $formatLevelMap + $this->formatLevelMap;
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($this->verbosityLevelMap[$level])) {
            $level = self::INFO;
        }

        // Write to the error output if necessary and available
        if ($this->formatLevelMap[$level] === self::ERROR && $this->output instanceof ConsoleOutputInterface) {
            $output = $this->output->getErrorOutput();
        } else {
            $output = $this->output;
        }

        if (isset($context['process'])) {
            $this->registerProcess($context);
        } else {
            $this->refresh($output);
        }

        if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $this->formatLevelMap[$level], $message));
        }
    }

    /**
     * Add deployment process to rendering stack
     *
     * @param array $context
     * @return void
     */
    private function registerProcess(array $context)
    {
        $name = isset($context['process']) ? $context['process'] : 'main';
        if (!isset($this->processes[$name])) {
            $context['start'] = time();
            $context['elapsed'] = 0;
            $this->processes[$name] = $context;
        }
    }

    /**
     * Refresh CLI output
     *
     * @param OutputInterface $output
     * @return void
     */
    private function refresh(OutputInterface $output)
    {
        if (!count($this->processes) || (time() - $this->lastTimeRefreshed < 1)) {
            return;
        }

        $this->cleanUp();

        $bars = [];
        $maxBarSize = 0;
        foreach ($this->processes as $name => & $process) {
            $this->updateProcessInfo($name, $process);
            $bar = $this->renderProgressBar($output, $process);
            $maxBarSize = strlen($bar) > $maxBarSize ? strlen($bar) : $maxBarSize;
            $bars[] = $bar;
        }
        if (!$this->initialMaxBarSize) {
            $this->initialMaxBarSize = $maxBarSize + 10;
        }
        if ($bars) {
            $this->renderedLines = count($bars);
            $bar = '';
            foreach ($bars as &$bar) {
                if ($this->initialMaxBarSize > strlen($bar)) {
                    $bar .= str_pad(" ", ($this->initialMaxBarSize - strlen($bar)));
                }
            }
            $bar = trim($bar);
            $output->writeln(implode("\n", $bars));
        }
    }

    /**
     * Update process information
     *
     * @param string $deployedPackagePath
     * @param array $process
     * @return void
     */
    private function updateProcessInfo($deployedPackagePath, array & $process)
    {
        $packageDeploymentInfo = $this->getPackageDeploymentInfo($deployedPackagePath . '/info.json');
        if ($packageDeploymentInfo) {
            $process['done'] = $packageDeploymentInfo['count'];
        } else {
            $process['done'] = 0;
        }
        if ($process['done'] > $process['count']) {
            $process['count'] = $process['done'];
        }
        if ($process['done'] !== $process['count']) {
            $process['elapsed'] = $this->formatterHelper->formatTime(time() - $process['start']);
        }
        $process['percent'] = floor(
            ($process['count'] ? (float)$process['done'] / $process['count'] : 0) * 100
        );
    }

    /**
     * Clear rendered lines
     *
     * @return void
     */
    private function cleanUp()
    {
        $this->lastTimeRefreshed = time();
        // Erase previous lines
        if ($this->renderedLines > 0) {
            for ($i = 0; $i < $this->renderedLines; ++$i) {
                $this->output->write("\x1B[1A\x1B[2K", false, OutputInterface::OUTPUT_RAW);
            }
        }
        $this->renderedLines = 0;
    }

    /**
     * Generate progress bar part
     *
     * @param OutputInterface $output
     * @param array $process
     * @return string
     */
    private function renderProgressBar(OutputInterface $output, array $process)
    {
        $title = "{$process['process']}";
        $titlePad = str_pad(' ', (40 - strlen($title)));
        $count = "{$process['done']}/{$process['count']}";
        $countPad = str_pad(' ', (20 - strlen($count)));
        $percent = "{$process['percent']}% ";
        $percentPad = str_pad(' ', (7 - strlen($percent)));
        return "{$title}{$titlePad}"
        . "{$count}{$countPad}"
        . "{$this->renderBar($output, $process)} "
        . "{$percent}{$percentPad}"
        . "{$process['elapsed']}   ";
    }

    /**
     * Generate progress bar row
     *
     * @param OutputInterface $output
     * @param array $process
     * @return string
     */
    private function renderBar(OutputInterface $output, array $process)
    {
        $completeBars = floor(
            $process['count'] > 0 ? ($process['done'] / $process['count']) * 28 : $process['done'] % 28
        );

        $display = str_repeat('=', $completeBars);
        if ($completeBars < 28) {
            $emptyBars = 28 - $completeBars
                - $this->formatterHelper->strlenWithoutDecoration($output->getFormatter(), '>');
            $display .= '>' . str_repeat('-', $emptyBars);
        }
        return $display;
    }

    /**
     * Retrieve package deployment process information
     *
     * @param string $relativePath
     * @return string|false
     */
    private function getPackageDeploymentInfo($relativePath)
    {
        if ($this->tmpDir->isFile($relativePath)) {
            $info = $this->tmpDir->readFile($relativePath);
            $info = json_decode($info, true);
        } else {
            $info = [];
        }
        return $info;
    }
}
