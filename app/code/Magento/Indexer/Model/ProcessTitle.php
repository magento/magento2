<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Laminas\Http\PhpEnvironment\Request as Environment;

/**
 * Provide functionality for setting CLI process title of indexation processes.
 */
class ProcessTitle
{
    private const string INDEXER = 'indexer';

    private const string PROCESS = 'process';

    private const string CONTEXT_SEPARATOR = ' # ';

    private const string ITEM_SEPARATOR = ', ';

    private const string VALUE_SEPARATOR = ': ';

    /**
     * @var string|null
     */
    private ?string $commandLine = null;

    /**
     * @var string|null
     */
    private ?string $baseTitle = null;

    /**
     * @var string[]
     */
    private array $context = [];

    /**
     * @param Environment $environment
     */
    public function __construct(private readonly Environment $environment)
    {
    }

    /**
     * Show the indexer being executed in the process title
     *
     * @param string $indexerId
     * @return void
     */
    public function setIndexerContext(string $indexerId): void
    {
        $this->setContext(self::INDEXER, $indexerId);
    }

    /**
     * Show the number of the child process in the process title
     *
     * @param int $processNumber
     * @return void
     */
    public function setProcessContext(int $processNumber): void
    {
        $this->setContext(self::PROCESS, $processNumber);
    }

    /**
     * Show the context value in the process title replacing the value set before for the same name
     *
     * @param string $name
     * @param string|int $value
     * @return void
     */
    public function setContext(string $name, string|int $value): void
    {
        if (!function_exists('cli_set_process_title')) {
            return;
        }

        $this->baseTitle ??= $this->getCurrentTitle();
        $this->context[$name] = (string)$value;
        cli_set_process_title($this->buildTitle());
    }

    /**
     * Restore the process title the process had before the context was set
     *
     * @return void
     */
    public function restore(): void
    {
        if ($this->baseTitle === null) {
            return;
        }

        cli_set_process_title($this->baseTitle);
        $this->baseTitle = null;
        $this->context = [];
    }

    /**
     * Build the process title from the base title and the context
     *
     * @return string
     */
    private function buildTitle(): string
    {
        $separator = str_contains($this->baseTitle, self::CONTEXT_SEPARATOR)
            ? self::ITEM_SEPARATOR
            : self::CONTEXT_SEPARATOR;

        return $this->baseTitle . $separator . implode(
            self::ITEM_SEPARATOR,
            array_map($this->formatContext(...), array_keys($this->context), $this->context)
        );
    }

    /**
     * Format the context value for the process title
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function formatContext(string $name, string $value): string
    {
        return $name . self::VALUE_SEPARATOR . $value;
    }

    /**
     * Get the current process title falling back to the command line the process has been started with
     *
     * @return string
     */
    private function getCurrentTitle(): string
    {
        $title = (string)cli_get_process_title();

        return $title !== '' ? $title : $this->getCommandLine();
    }

    /**
     * Get the command line the current process has been started with
     *
     * @return string
     */
    private function getCommandLine(): string
    {
        return $this->commandLine ??= PHP_BINARY . ' '
            . implode(' ', (array)$this->environment->getServer('argv', []));
    }
}
