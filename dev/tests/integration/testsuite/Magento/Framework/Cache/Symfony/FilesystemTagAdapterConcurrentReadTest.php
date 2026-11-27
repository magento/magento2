<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\FilesystemTagAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * A tag index read never sees a partly rewritten tag file while other processes add ids to it.
 */
class FilesystemTagAdapterConcurrentReadTest extends TestCase
{
    private const TAG = 'CONCURRENT_READ_TAG';
    private const INITIAL_IDS = 3000;
    private const WRITERS = 4;
    private const IDS_PER_WRITER = 200;
    private const TIMEOUT_SECONDS = 120;

    /**
     * @var string
     */
    private $directory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (!function_exists('pcntl_fork') || !function_exists('posix_kill')) {
            $this->markTestSkipped('The pcntl and posix extensions are required to run concurrent writers.');
        }
        $this->directory = sys_get_temp_dir() . '/tag-adapter-read-' . uniqid('', true);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->directory && is_dir($this->directory)) {
            foreach (['tags', 'idtags'] as $subDirectory) {
                $path = $this->directory . '/' . $subDirectory;
                if (is_dir($path)) {
                    array_map('unlink', glob($path . '/*') ?: []);
                    rmdir($path);
                }
            }
            rmdir($this->directory);
        }
    }

    /**
     * Ids are only added, so every read must return at least the ids that were there before the writers started.
     *
     * @return void
     */
    public function testReadDuringConcurrentWritesSeesAllExistingIds(): void
    {
        $adapter = $this->createAdapterWithInitialIds();
        $this->assertCount(self::INITIAL_IDS, $adapter->getIdsMatchingTags([self::TAG]));

        [$reads, $shortReads] = $this->readWhileWriting($adapter, $this->startWriters());

        $this->assertSame(
            0,
            $shortReads,
            sprintf(
                '%d of %d reads during concurrent writes returned fewer ids than the index held',
                $shortReads,
                $reads
            )
        );
        $this->assertCount(
            self::INITIAL_IDS + self::WRITERS * self::IDS_PER_WRITER,
            $adapter->getIdsMatchingTags([self::TAG])
        );
    }

    /**
     * Create an adapter whose tag index already holds the initial ids
     *
     * @return FilesystemTagAdapter
     */
    private function createAdapterWithInitialIds(): FilesystemTagAdapter
    {
        $adapter = new FilesystemTagAdapter(new ArrayAdapter(), $this->directory);
        for ($i = 0; $i < self::INITIAL_IDS; $i++) {
            $adapter->onSave('initial_' . $i, [self::TAG]);
        }

        return $adapter;
    }

    /**
     * Fork writer processes that add their own ids to the tag
     *
     * @return int[] Process ids of the writers
     */
    private function startWriters(): array
    {
        $pids = [];
        for ($writer = 0; $writer < self::WRITERS; $writer++) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('pcntl_fork failed');
            }
            if ($pid === 0) {
                $this->runWriter($writer);
            }
            $pids[] = $pid;
        }

        return $pids;
    }

    /**
     * Save the ids of one writer in a child process, then end the process without running PHPUnit shutdown code.
     * The process ends with SIGKILL only when all ids were saved, and with SIGTERM otherwise.
     *
     * @param int $writer
     * @return void
     */
    private function runWriter(int $writer): void
    {
        $signal = SIGTERM;
        try {
            $adapter = new FilesystemTagAdapter(new ArrayAdapter(), $this->directory);
            for ($i = 0; $i < self::IDS_PER_WRITER; $i++) {
                $adapter->onSave('writer_' . $writer . '_' . $i, [self::TAG]);
            }
            $signal = SIGKILL;
        } finally {
            posix_kill(getmypid(), $signal);
        }
    }

    /**
     * Read the tag until all writers have finished, counting reads that miss ids present before the writers started
     *
     * @param FilesystemTagAdapter $adapter
     * @param int[] $pids
     * @return int[] Number of reads and number of short reads
     */
    private function readWhileWriting(FilesystemTagAdapter $adapter, array $pids): array
    {
        $reads = 0;
        $shortReads = 0;
        $deadline = time() + self::TIMEOUT_SECONDS;
        while ($pids) {
            if (time() > $deadline) {
                array_map(static fn(int $pid) => posix_kill($pid, SIGKILL), $pids);
                $this->fail('The writer processes did not finish in time');
            }
            $reads++;
            if (count($adapter->getIdsMatchingTags([self::TAG])) < self::INITIAL_IDS) {
                $shortReads++;
            }
            foreach ($pids as $key => $pid) {
                $status = 0;
                if (pcntl_waitpid($pid, $status, WNOHANG) !== 0) {
                    $this->assertSame(SIGKILL, pcntl_wtermsig($status), 'A writer process failed');
                    unset($pids[$key]);
                }
            }
        }

        return [$reads, $shortReads];
    }
}
