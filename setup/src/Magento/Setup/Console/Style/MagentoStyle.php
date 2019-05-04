<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Style;

use Magento\Setup\Console\InputValidationException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Terminal;

/**
 * Magento console output decorator.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MagentoStyle extends OutputStyle implements MagentoStyleInterface
{
    /**
     * Default console line max length(use for limitation in case terminal width greater than 120 characters).
     */
    const MAX_LINE_LENGTH = 120;

    /**
     * Console input provider.
     *
     * @var InputInterface
     */
    private $input;

    /**
     * Style Guide compliant question helper.
     *
     * @var SymfonyQuestionHelper
     */
    private $questionHelper;

    /**
     * Progress output provider.
     *
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * Calculated output line length.
     *
     * @var int
     */
    private $lineLength;

    /**
     * Console output buffer provider.
     *
     * @var BufferedOutput
     */
    private $bufferedOutput;

    /**
     * MagentoStyle constructor.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->bufferedOutput = new BufferedOutput($output->getVerbosity(), false, clone $output->getFormatter());
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $currentLength = $this->getTerminalWidth() - (int)(DIRECTORY_SEPARATOR === '\\');
        $this->lineLength = min($currentLength, self::MAX_LINE_LENGTH);
        parent::__construct($output);
    }

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages The message to write in the block
     * @param string|null $type The block type (added in [] on first line)
     * @param string|null $style The style to apply to the whole block
     * @param string $prefix The prefix for the block
     * @param bool $padding Whether to add vertical padding
     * @return void
     */
    public function block(
        $messages,
        string $type = null,
        string $style = null,
        string $prefix = ' ',
        bool $padding = false
    ) {
        $messages = is_array($messages) ? array_values($messages) : [$messages];
        $this->autoPrependBlock();
        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding));
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function title($message)
    {
        $this->autoPrependBlock();
        $bar = str_repeat('=', Helper::strlenWithoutDecoration($this->getFormatter(), $message));
        $this->writeln([
            sprintf(' <options=bold>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
            sprintf(' <options=bold>%s</>', $bar),
        ]);
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function section($message)
    {
        $this->autoPrependBlock();
        $bar = str_repeat('-', Helper::strlenWithoutDecoration($this->getFormatter(), $message));
        $this->writeln([
            sprintf(' <fg=white>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
            sprintf(' <fg=white>%s</>', $bar),
        ]);
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function listing(array $elements)
    {
        $this->autoPrependText();
        $elements = array_map(function ($element) {
            return sprintf(' * %s', $element);
        }, $elements);

        $this->writeln($elements);
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function text($message)
    {
        $this->autoPrependText();
        $messages = is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $singleMessage) {
            $this->writeln(sprintf(' %s', $singleMessage));
        }
    }

    /**
     * Formats a command comment.
     *
     * @param string|array $message
     * @param bool $padding
     * @return void
     */
    public function comment($message, $padding = false)
    {
        $this->block($message, null, 'comment', ' ', $padding);
    }

    /**
     * {@inheritdoc}
     */
    public function success($message, $padding = true)
    {
        $this->block($message, 'SUCCESS', 'fg=black;bg=green', ' ', $padding);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, $padding = true)
    {
        $this->block($message, 'ERROR', 'fg=white;bg=red', ' ', $padding);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, $padding = true)
    {
        $this->block($message, 'WARNING', 'fg=black;bg=yellow', ' ', $padding);
    }

    /**
     * {@inheritdoc}
     */
    public function note($message, $padding = false)
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ', $padding);
    }

    /**
     * {@inheritdoc}
     */
    public function caution($message, $padding = true)
    {
        $this->block($message, 'CAUTION', 'fg=black;bg=yellow', ' ! ', $padding);
    }

    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows)
    {
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);

        $table->render();
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function ask($question, $default = null, $validator = null, $maxAttempts = null)
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);
        $question->setMaxAttempts($maxAttempts);

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function askHidden($question, $validator = null)
    {
        $question = new Question($question);

        $question->setHidden(true);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     */
    public function confirm($question, $default = true)
    {
        return $this->askQuestion(new ConfirmationQuestion($question, $default));
    }

    /**
     * {@inheritdoc}
     */
    public function choice($question, array $choices, $default = null)
    {
        if (null !== $default) {
            $values = array_flip($choices);
            $default = $values[$default];
        }

        return $this->askQuestion(new ChoiceQuestion($question, $choices, $default));
    }

    /**
     * {@inheritdoc}
     */
    public function progressStart($max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    public function progressAdvance($step = 1)
    {
        $this->getProgressBar()->advance($step);
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    public function progressFinish()
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        $this->progressBar = null;
    }

    /**
     * {@inheritdoc}
     */
    public function createProgressBar($max = 0)
    {
        $progressBar = parent::createProgressBar($max);
        $progressBar->setEmptyBarCharacter(' ');
        $progressBar->setProgressCharacter('>');
        $progressBar->setBarCharacter('=');

        return $progressBar;
    }

    /**
     * Ask user question.
     *
     * @param Question $question
     *
     * @return string
     */
    public function askQuestion(Question $question)
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }

        if (!$this->questionHelper) {
            $this->questionHelper = new SymfonyQuestionHelper();
        }

        $answer = $this->questionHelper->ask($this->input, $this, $question);

        if ($this->input->isInteractive()) {
            $this->newLine();
            $this->bufferedOutput->write(PHP_EOL);
        }

        return $answer;
    }

    /**
     * Ask for an missing argument.
     *
     * @param string $argument
     * @param string $question
     * @param string|null $default
     * @param callable|null $validator
     * @param int|null $maxAttempts
     * @param bool $comment
     * @param string $commentFormat
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function askForMissingArgument(
        string $argument,
        string $question,
        string $default = null,
        callable $validator = null,
        int $maxAttempts = null,
        bool $comment = null,
        string $commentFormat = 'Argument [%s] set to: %s'
    ) {
        try {
            if ($this->input->getArgument($argument) === null) {
                $this->input->setArgument($argument, $this->ask($question, $default, $validator, $maxAttempts));
            }
            $argumentValue = $this->input->getArgument($argument);
            $validated = (is_callable($validator) ? $validator($argumentValue) : $argumentValue);
            if ((bool)($comment ?? $this->isDebug())) {
                $this->comment(sprintf($commentFormat, $argument, $validated));
            }
        } catch (InputValidationException $e) {
            $this->error('Validation Error: ' . $e->getMessage());
            $this->askForMissingArgument(
                $argument,
                $question,
                $default,
                $validator,
                $maxAttempts,
                $comment,
                $commentFormat
            );
        }
    }

    /**
     * Ask for an missing option.
     *
     * @param string $option
     * @param string $question
     * @param string|null $default
     * @param callable|null $validator
     * @param int|null $maxAttempts
     * @param bool $comment
     * @param string $commentFormat
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function askForMissingOption(
        string $option,
        string $question,
        string $default = null,
        callable $validator = null,
        int $maxAttempts = null,
        bool $comment = null,
        string $commentFormat = 'Option [%s] set to: %s'
    ) {
        try {
            if (null === $this->input->getOption($option)) {
                $this->input->setOption($option, $this->ask($question, $default, $validator, $maxAttempts));
            }
            $optionValue = $this->input->getOption($option);
            $validated = (is_callable($validator) ? $validator($optionValue) : $optionValue);
            if ((bool)($comment ?? $this->isDebug())) {
                $this->comment(sprintf($commentFormat, $option, $validated));
            }
        } catch (InputValidationException $e) {
            $this->error('Validation Error: ' . $e->getMessage());
            $this->askForMissingOption(
                $option,
                $question,
                $default,
                $validator,
                $maxAttempts,
                $comment,
                $commentFormat
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        parent::writeln($messages, $type);
        $this->bufferedOutput->writeln($this->reduceBuffer($messages), $type);
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        parent::write($messages, $newline, $type);
        $this->bufferedOutput->write($this->reduceBuffer($messages), $newline, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function newLine($count = 1)
    {
        parent::newLine($count);
        $this->bufferedOutput->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * Get progress bar instance.
     *
     * @return ProgressBar
     * @throws RuntimeException in case progress bar hasn't been instantiated yet.
     */
    private function getProgressBar()
    {
        if (!$this->progressBar) {
            throw new RuntimeException('The ProgressBar is not started.');
        }

        return $this->progressBar;
    }

    /**
     * @return int
     */
    private function getTerminalWidth()
    {
        $terminal = new Terminal();
        $width = $terminal->getWidth();

        return $width ?: self::MAX_LINE_LENGTH;
    }

    /**
     * Add empty line before output element in case there were no empty lines before.
     *
     * @return void
     */
    private function autoPrependBlock()
    {
        $chars = substr($this->bufferedOutput->fetch(), -2);
        if (!isset($chars[0])) {
            $this->newLine(); //empty history, so we should start with a new line.
        }
        //Prepend new line for each non LF chars (This means no blank line was output before)
        $this->newLine(2 - substr_count($chars, PHP_EOL));
    }

    /**
     * Add empty line before text(listing) output element.
     *
     * @return void
     */
    private function autoPrependText()
    {
        $fetched = $this->bufferedOutput->fetch();
        //Prepend new line if last char isn't EOL:
        if (PHP_EOL !== substr($fetched, -1)) {
            $this->newLine();
        }
    }

    /**
     * @param array $messages
     * @return array
     */
    private function reduceBuffer($messages)
    {
        // We need to know if the two last chars are PHP_EOL
        // Preserve the last 4 chars inserted (PHP_EOL on windows is two chars) in the history buffer
        return array_map(function ($value) {
            return substr($value, -4);
        }, array_merge([$this->bufferedOutput->fetch()], (array)$messages));
    }

    /**
     * Build output in block style.
     *
     * @param array $messages
     * @param string|null $type
     * @param string|null $style
     * @param string $prefix
     * @param bool $padding
     * @return array
     */
    private function createBlock(
        array $messages,
        string $type = null,
        string $style = null,
        string $prefix = ' ',
        bool $padding = false
    ) {
        $indentLength = 0;
        $prefixLength = Helper::strlenWithoutDecoration($this->getFormatter(), $prefix);
        if (null !== $type) {
            $type = sprintf('[%s] ', $type);
            $indentLength = strlen($type);
            $lineIndentation = str_repeat(' ', $indentLength);
        }
        $lines = $this->getBlockLines($messages, $prefixLength, $indentLength);
        $firstLineIndex = 0;
        if ($padding && $this->isDecorated()) {
            $firstLineIndex = 1;
            array_unshift($lines, '');
            $lines[] = '';
        }
        foreach ($lines as $i => &$line) {
            if (null !== $type) {
                $line = $firstLineIndex === $i ? $type . $line : $lineIndentation . $line;
            }
            $line = $prefix . $line;
            $multiplier = $this->lineLength - Helper::strlenWithoutDecoration($this->getFormatter(), $line);
            $line .= str_repeat(' ', $multiplier);
            if ($style) {
                $line = sprintf('<%s>%s</>', $style, $line);
            }
        }

        return $lines;
    }

    /**
     * Wrap and add new lines for each element.
     *
     * @param array $messages
     * @param int $prefixLength
     * @param int $indentLength
     * @return array
     */
    private function getBlockLines(
        array $messages,
        int $prefixLength,
        int $indentLength
    ) {
        $lines = [[]];
        foreach ($messages as $key => $message) {
            $message = OutputFormatter::escape($message);
            $wordwrap = wordwrap($message, $this->lineLength - $prefixLength - $indentLength, PHP_EOL, true);
            $lines[] = explode(PHP_EOL, $wordwrap);
            if (count($messages) > 1 && $key < count($messages) - 1) {
                $lines[][] = '';
            }
        }
        $lines = array_merge(...$lines);

        return $lines;
    }
}
